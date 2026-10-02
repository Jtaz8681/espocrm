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

namespace Espo\Classes\Acl\User;

use Espo\Core\Acl\Permission;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityCREDSChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\AclManager;

/**
 * @implements AccessEntityCREDSChecker<User>
 */
class AccessChecker implements AccessEntityCREDSChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(
        private DefaultAccessChecker $defaultAccessChecker,
        private AclManager $aclManager,
    ) {}

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        if ($entity->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityCreate($user, $entity, $data);
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$user->isAdmin() && !$entity->isActive()) {
            return false;
        }

        if ($entity->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        if ($entity->isSystem()) {
            return false;
        }

        if ($entity->isPortal()) {
            return $this->aclManager->getPermissionLevel($user, Permission::PORTAL) === Table::LEVEL_YES;
        }

        return $this->defaultAccessChecker->checkEntityRead($user, $entity, $data);
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->isSystem()) {
            return false;
        }

        if (!$user->isAdmin()) {
            if ($user->getId() !== $entity->getId()) {
                return false;
            }
        }

        if ($entity->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityEdit($user, $entity, $data);
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        if ($entity->isSystem()) {
            return false;
        }

        if ($entity->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityDelete($user, $entity, $data);
    }

    public function checkEntityStream(User $user, Entity $entity, ScopeData $data): bool
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->aclManager->checkUserPermission($user, $entity, Permission::USER);
    }
}
