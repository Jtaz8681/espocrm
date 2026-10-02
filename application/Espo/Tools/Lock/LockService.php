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

namespace Espo\Tools\Lock;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\ErrorSilent;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\MassAction\Result;
use Espo\Core\Name\Field;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class LockService
{
    public function __construct(
        private Acl $acl,
        private EntityManager $entityManager,
        private LockMetadataProvider $lockMetadataProvider,
    ) {}

    /**
     * @param Collection<Entity> $collection
     */
    public function massLock(Collection $collection): Result
    {
        $count = 0;

        foreach ($collection as $entity) {
            try {
                $this->lock($entity);
            } catch (Error|Forbidden|BadRequest) {
                continue;
            }

            $count ++;
        }

        return new Result(count: $count);
    }

    /**
     * @param Collection<Entity> $collection
     */
    public function massUnlock(Collection $collection): Result
    {
        $count = 0;

        foreach ($collection as $entity) {
            try {
                $this->unlock($entity);
            } catch (Error|Forbidden|BadRequest) {
                continue;
            }

            $count ++;
        }

        return new Result(count: $count);
    }

    public function isEnabled(string $entityType): bool
    {
        return $this->lockMetadataProvider->isEnabled($entityType);
    }

    public function isAllowed(string $entityType): bool
    {
        if (!$this->acl->checkScope($entityType, Acl\Table::ACTION_EDIT)) {
            return false;
        }

        if ($this->acl->getPermissionLevel(Acl\Permission::LOCK) !== Acl\Table::LEVEL_YES) {
            return false;
        }

        return true;
    }

    /**
     * @throws Error
     * @throws Forbidden
     * @throws BadRequest
     */
    public function lock(Entity $entity): void
    {
        $this->check($entity);

        if ($entity->get(Field::IS_LOCKED)) {
            throw new BadRequest("Already locked.");
        }

        $this->lockEntity($entity);
    }

    /**
     * @throws Error
     * @throws Forbidden
     * @throws BadRequest
     */
    public function unlock(Entity $entity): void
    {
        $this->check($entity);

        if (!$entity->get(Field::IS_LOCKED)) {
            throw new BadRequest("Already unlocked.");
        }

        $this->unlockEntity($entity);
    }

    private function lockEntity(Entity $entity): void
    {
        if ($entity->get(Field::IS_LOCKED)) {
            return;
        }

        $entity->set(Field::IS_LOCKED, true);

        $this->entityManager->saveEntity($entity);

    }

    private function unlockEntity(Entity $entity): void
    {
        if (!$entity->get(Field::IS_LOCKED)) {
            return;
        }

        $entity->set(Field::IS_LOCKED, false);

        $this->entityManager->saveEntity($entity);
    }

    /**
     * @throws Error
     * @throws Forbidden
     */
    private function check(Entity $entity): void
    {
        if (!$this->isEnabled($entity->getEntityType())) {
            throw new ErrorSilent("Lock is not enabled.");
        }

        if ($this->acl->getPermissionLevel(Acl\Permission::LOCK) !== Acl\Table::LEVEL_YES) {
            throw new ForbiddenSilent("Not lock permission.");
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new ForbiddenSilent("No edit access.");
        }
    }
}
