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

namespace Espo\Core\Acl\AccessChecker\AccessCheckers;

use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Utils\Metadata;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\Acl\AccessEntityCreateChecker;
use Espo\Core\Acl\AccessEntityReadChecker;
use Espo\Core\Acl\AccessEntityEditChecker;
use Espo\Core\Acl\AccessEntityDeleteChecker;
use Espo\Core\Acl\AccessEntityStreamChecker;
use Espo\Core\Acl\ScopeData;

use Espo\ORM\EntityManager;

use LogicException;

/**
 * Access is determined by access to a foreign entity.
 *
 * @implements AccessEntityCreateChecker<Entity>
 * @implements AccessEntityReadChecker<Entity>
 * @implements AccessEntityEditChecker<Entity>
 * @implements AccessEntityDeleteChecker<Entity>
 * @implements AccessEntityStreamChecker<Entity>
 */
class Foreign implements

    AccessEntityCreateChecker,
    AccessEntityReadChecker,
    AccessEntityEditChecker,
    AccessEntityDeleteChecker,
    AccessEntityStreamChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(
        private Metadata $metadata,
        DefaultAccessChecker $defaultAccessChecker,
        private EntityManager $entityManager
    ) {
        $this->defaultAccessChecker = $defaultAccessChecker;
    }

    private function getForeignEntity(Entity $entity): ?Entity
    {
        $entityType = $entity->getEntityType();

        $link = $this->metadata->get(['aclDefs', $entityType, 'link']);

        if (!$link) {
            throw new LogicException("No `link` in aclDefs for {$entityType}.");
        }

        if ($entity->isNew()) {
            $foreignEntityType = $this->entityManager
                ->getDefs()
                ->getEntity($entityType)
                ->getRelation($link)
                ->getForeignEntityType();

            /** @var ?string $id */
            $id = $entity->get($link . 'Id');

            if (!$id) {
                return null;
            }

            return $this->entityManager->getEntityById($foreignEntityType, $id);
        }

        return $this->entityManager
            ->getRDBRepository($entityType)
            ->getRelation($entity, $link)
            ->findOne();
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        $foreign = $this->getForeignEntity($entity);

        if (!$foreign) {
            return false;
        }

        // @todo Check parent 'edit' access.

        return $this->defaultAccessChecker->checkEntityCreate($user, $foreign, $data);
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        $foreign = $this->getForeignEntity($entity);

        if (!$foreign) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityRead($user, $foreign, $data);
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        $foreign = $this->getForeignEntity($entity);

        if (!$foreign) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityEdit($user, $foreign, $data);
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        $foreign = $this->getForeignEntity($entity);

        if (!$foreign) {
            if ($user->isAdmin()) {
                return true;
            }

            return false;
        }

        return $this->defaultAccessChecker->checkEntityDelete($user, $foreign, $data);
    }

    public function checkEntityStream(User $user, Entity $entity, ScopeData $data): bool
    {
        $foreign = $this->getForeignEntity($entity);

        if (!$foreign) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityStream($user, $foreign, $data);
    }
}
