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

namespace Espo\Tools\Export;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Tools\Export\Jobs\Process;
use Espo\ORM\EntityManager;
use Espo\Entities\Export as ExportEntity;
use Espo\Entities\User;

use stdClass;

class Service
{
    public function __construct(
        private Factory $factory,
        private Config $config,
        private Acl $acl,
        private User $user,
        private Metadata $metadata,
        private EntityManager $entityManager,
        private JobSchedulerFactory $jobSchedulerFactory
    ) {}

    /**
     * @throws Forbidden
     */
    public function process(Params $params, ServiceParams $serviceParams): ServiceResult
    {
        if ($this->config->get('exportDisabled') && !$this->user->isAdmin()) {
            throw new ForbiddenSilent("Export disabled for non-admin users.");
        }

        $entityType = $params->getEntityType();

        if ($this->acl->getPermissionLevel(Acl\Permission::EXPORT) !== Table::LEVEL_YES) {
            throw new ForbiddenSilent("No 'export' permission.");
        }

        if (!$this->acl->check($entityType, Table::ACTION_READ)) {
            throw new ForbiddenSilent("No 'read' access.");
        }

        if ($this->metadata->get(['recordDefs', $entityType, 'exportDisabled'])) {
            throw new ForbiddenSilent("Export disabled for '$entityType'.");
        }

        if ($serviceParams->isIdle()) {
            if ($this->user->isPortal()) {
                throw new ForbiddenSilent("Idle export is not allowed for portal users.");
            }

            return $this->schedule($params);
        }

        $export = $this->factory->create();

        $result = $export
            ->setParams($params)
            ->run();

        return ServiceResult::createWithResult($result);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function getStatusData(string $id): stdClass
    {
        /** @var ?ExportEntity $entity */
        $entity = $this->entityManager->getEntityById(ExportEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFoundSilent();
        }

        if ($entity->getCreatedBy()->getId() !== $this->user->getId()) {
            throw new ForbiddenSilent();
        }

        return (object) [
            'status' => $entity->getStatus(),
            'attachmentId' => $entity->getAttachmentId(),
        ];
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function subscribeToNotificationOnSuccess(string $id): void
    {
        /** @var ?ExportEntity $entity */
        $entity = $this->entityManager->getEntityById(ExportEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFoundSilent();
        }

        if ($entity->getCreatedBy()->getId() !== $this->user->getId()) {
            throw new ForbiddenSilent();
        }

        $entity->setNotifyOnFinish();

        $this->entityManager->saveEntity($entity);
    }

    private function schedule(Params $params): ServiceResult
    {
        $entity = $this->entityManager->createEntity(ExportEntity::ENTITY_TYPE, [
            // Additional encoding to handle null-character issue in PostgreSQL.
            'params' => base64_encode(serialize($params)),
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
