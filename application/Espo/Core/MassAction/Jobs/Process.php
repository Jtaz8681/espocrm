<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace Espo\Core\MassAction\Jobs;

use Espo\Core\Exceptions\Error;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Core\MassAction\MassActionFactory;
use Espo\Core\MassAction\Result;
use Espo\Core\Utils\Language;
use Espo\ORM\EntityManager;
use Espo\Entities\MassAction as MassActionEntity;
use Espo\Entities\Notification;
use Espo\Entities\User;

use Throwable;

class Process implements Job
{
    public function __construct(
        private EntityManager $entityManager,
        private MassActionFactory $factory,
        private Language $language
    ) {}

    /**
     * @throws Error
     */
    public function run(JobData $data): void
    {
        $id = $data->getTargetId();

        if ($id === null) {
            throw new Error("ID not passed to the mass action job.");
        }

        /** @var MassActionEntity|null $entity */
        $entity = $this->entityManager->getEntityById(MassActionEntity::ENTITY_TYPE, $id);

        if ($entity === null) {
            throw new Error("MassAction '$id' not found.");
        }

        /** @var User|null $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $entity->getCreatedBy()->getId());

        if (!$user) {
            throw new Error("MassAction '$id', user not found.");
        }

        $params = $entity->getParams();

        try {
            $massAction = $this->factory->createForUser($entity->getAction(), $params->getEntityType(), $user);

            $this->setRunning($entity);

            $result = $massAction->process(
                $params,
                $entity->getData()
            );
        } catch (Throwable $e) {
            $this->setFailed($entity);

            throw new Error("Mass action job error: " . $e->getMessage());
        }

        $this->setSuccess($entity, $result);

        $this->entityManager->refreshEntity($entity);

        if ($entity->notifyOnFinish()) {
            $this->notifyFinish($entity);
        }
    }

    private function notifyFinish(MassActionEntity $entity): void
    {
        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $message = $this->language->translateLabel('massActionProcessed', 'messages');

        $notification
            ->setType(Notification::TYPE_MESSAGE)
            ->setMessage($message)
            ->setUserId($entity->getCreatedBy()->getId());

        $this->entityManager->saveEntity($notification);
    }

    private function setFailed(MassActionEntity $entity): void
    {
        $entity->setStatus(MassActionEntity::STATUS_FAILED);

        $this->entityManager->saveEntity($entity);
    }

    private function setRunning(MassActionEntity $entity): void
    {
        $entity->setStatus(MassActionEntity::STATUS_RUNNING);

        $this->entityManager->saveEntity($entity);
    }

    private function setSuccess(MassActionEntity $entity, Result $result): void
    {
        $entity
            ->setStatus(MassActionEntity::STATUS_SUCCESS)
            ->setProcessedCount($result->getCount());

        $this->entityManager->saveEntity($entity);
    }
}
