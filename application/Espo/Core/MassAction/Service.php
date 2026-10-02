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

namespace Espo\Core\MassAction;

use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\EntityManager;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Acl;
use Espo\Core\MassAction\Jobs\Process;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Entities\User;
use Espo\Entities\MassAction as MassActionEntity;

use stdClass;

class Service
{
    public function __construct(
        private MassActionFactory $factory,
        private Acl $acl,
        private JobSchedulerFactory $jobSchedulerFactory,
        private EntityManager $entityManager,
        private User $user
    ) {}

    /**
     * Perform a mass action.
     *
     * @throws Forbidden
     * @throws NotFound
     * @throws BadRequest
     */
    public function process(
        string $entityType,
        string $action,
        ServiceParams $serviceParams,
        stdClass $data
    ): ServiceResult {

        if (!$this->acl->checkScope($entityType)) {
            throw new ForbiddenSilent();
        }

        $params = $serviceParams->getParams();

        if ($serviceParams->isIdle()) {
            if ($this->user->isPortal()) {
                throw new Forbidden("Idle mass actions are not allowed for portal users.");
            }

            return $this->schedule($entityType, $action, $params, $data);
        }

        $massAction = $this->factory->create($action, $entityType);

        $result = $massAction->process(
            $params,
            Data::fromRaw($data)
        );

        if ($params->hasIds()) {
            return ServiceResult::createWithResult($result);
        }

        return ServiceResult::createWithResult(
            $result->withNoIds()
        );
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function getStatusData(string $id): stdClass
    {
        /** @var ?MassActionEntity $entity */
        $entity = $this->entityManager->getEntityById(MassActionEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if ($entity->getCreatedBy()->getId() !== $this->user->getId()) {
            throw new Forbidden();
        }

        return (object) [
            'status' => $entity->getStatus(),
            'processedCount' => $entity->getProcessedCount(),
        ];
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function subscribeToNotificationOnSuccess(string $id): void
    {
        /** @var ?MassActionEntity $entity */
        $entity = $this->entityManager->getEntityById(MassActionEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if ($entity->getCreatedBy()->getId() !== $this->user->getId()) {
            throw new Forbidden();
        }

        $entity->setNotifyOnFinish();

        $this->entityManager->saveEntity($entity);
    }

    private function schedule(string $entityType, string $action, Params $params, stdClass $data): ServiceResult
    {
        $entity = $this->entityManager->createEntity(MassActionEntity::ENTITY_TYPE, [
            'entityType' => $entityType,
            'action' => $action,
            // Additional encoding to handle null-character issue in PostgreSQL.
            'params' => base64_encode(serialize($params)),
            'data' => $data,
        ]);

        $this->jobSchedulerFactory
            ->create()
            ->setClassName(Process::class)
            ->setData(
                JobData::create()
                    ->withTargetId($entity->getId())
                    ->withTargetType($entity->getEntityType())
            )
            ->schedule();

        return ServiceResult::createWithId($entity->getId());
    }
}
