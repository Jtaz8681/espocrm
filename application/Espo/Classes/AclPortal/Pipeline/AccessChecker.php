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

namespace Espo\Classes\AclPortal\Pipeline;

use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Portal\AclManager as PortalAclManager;
use Espo\Entities\Pipeline;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements AccessEntityCREDChecker<Pipeline>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    public function __construct(
        private PortalAclManager $aclManager
    ) {}

    public function check(User $user, ScopeData $data): bool
    {
        return $data->isTrue();
    }

    public function checkCreate(User $user, ScopeData $data): bool
    {
        return false;
    }

    public function checkRead(User $user, ScopeData $data): bool
    {
        return $data->isTrue();
    }

    public function checkEdit(User $user, ScopeData $data): bool
    {
        return false;
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        return false;
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        return false;
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        return false;
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        return false;
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$data->isFalse()) {
            return false;
        }

        if (!$this->aclManager->checkScope($user, $entity->getTargetEntityType())) {
            return false;
        }

        if ($entity->isAvailableForAll()) {
            return true;
        }

        return false;
    }
}
