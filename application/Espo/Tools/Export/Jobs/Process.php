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

namespace Espo\Tools\Export\Jobs;

use Espo\Core\Exceptions\Error;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Tools\Export\Factory;
use Espo\Tools\Export\Result;
use Espo\Core\Utils\Language;
use Espo\ORM\EntityManager;
use Espo\Entities\Export;
use Espo\Entities\Notification;
use Espo\Entities\User;

use Throwable;

class Process implements Job
{
    public function __construct(
        private EntityManager $entityManager,
        private Factory $factory,
        private Language $language,
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

        /** @var Export|null $entity */
        $entity = $this->entityManager->getEntityById(Export::ENTITY_TYPE, $id);

        if ($entity === null) {
            throw new Error("Export '$id' not found.");
        }

        /** @var User|null $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $entity->getCreatedBy()->getId());

        if (!$user) {
            throw new Error("Export entity '$id', user not found.");
        }

        try {
            $export = $this->factory->createForUser($user);

            $this->setRunning($entity);

            $result = $export
                ->setParams($entity->getParams())
                ->run();
        } catch (Throwable $e) {
            $this->setFailed($entity);

            throw new Error("Export job error: " . $e->getMessage());
        }

        $this->setSuccess($entity, $result);

        $this->entityManager->refreshEntity($entity);

        if ($entity->notifyOnFinish()) {
            $this->notifyFinish($entity);
        }
    }

    private function notifyFinish(Export $entity): void
    {
        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $url = '?entryPoint=download&id=' . $entity->getAttachmentId();

        $message = str_replace(
            '{url}',
            $url,
            $this->language->translateLabel('exportProcessed', 'messages', 'Export')
        );

        $notification
            ->setType(Notification::TYPE_MESSAGE)
            ->setMessage($message)
            ->setUserId($entity->getCreatedBy()->getId());

        $this->entityManager->saveEntity($notification);
    }

    private function setFailed(Export $entity): void
    {
        $entity->setStatus(Export::STATUS_FAILED);

        $this->entityManager->saveEntity($entity);
    }

    private function setRunning(Export $entity): void
    {
        $entity->setStatus(Export::STATUS_RUNNING);

        $this->entityManager->saveEntity($entity);
    }

    private function setSuccess(Export $entity, Result $result): void
    {
        $entity
            ->setStatus(Export::STATUS_SUCCESS)
            ->setAttachmentId($result->getAttachmentId());

        $this->entityManager->saveEntity($entity);
    }
}
