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

namespace Espo\Classes\Acl\Import;

use Espo\Entities\Import;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityDeleteChecker;
use Espo\Core\Acl\AccessEntityReadChecker;
use Espo\Core\Acl\ScopeData;

/**
 * @implements AccessEntityReadChecker<Import>
 * @implements AccessEntityDeleteChecker<Import>
 */
class AccessChecker implements AccessEntityReadChecker, AccessEntityDeleteChecker
{
    public function check(User $user, ScopeData $data): bool
    {
        return $data->isTrue();
    }

    public function checkRead(User $user, ScopeData $data): bool
    {
        return $data->isTrue();
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        return $data->isTrue();
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->getId() === $entity->get('createdById')) {
            return true;
        }

        return false;
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->getId() === $entity->get('createdById')) {
            return true;
        }

        return false;
    }
}
