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

namespace Espo\Classes\Acl\ScheduledJob;

use Espo\Entities\ScheduledJob;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;

/**
 * @implements AccessEntityCREDChecker<ScheduledJob>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    private DefaultAccessChecker $defaultAccessChecker;

    public function __construct(DefaultAccessChecker $defaultAccessChecker)
    {
        $this->defaultAccessChecker = $defaultAccessChecker;
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->get('isInternal')) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityCreate($user, $entity, $data);
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->get('isInternal')) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityRead($user, $entity, $data);
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->get('isInternal')) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityEdit($user, $entity, $data);
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->get('isInternal')) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityDelete($user, $entity, $data);
    }
}
