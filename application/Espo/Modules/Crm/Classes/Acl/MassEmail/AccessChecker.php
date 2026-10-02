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

namespace Espo\Modules\Crm\Classes\Acl\MassEmail;

use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\AclManager;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;

/**
 * @implements AccessEntityCREDChecker<MassEmail>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(
        DefaultAccessChecker $defaultAccessChecker,
        private AclManager $aclManager,
    ) {
        $this->defaultAccessChecker = $defaultAccessChecker;
    }

    public function checkCreate(User $user, ScopeData $data): bool
    {
        return $this->checkEdit($user, $data);
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        return $this->checkEdit($user, $data) || $this->defaultAccessChecker->checkDelete($user, $data);
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($this->defaultAccessChecker->checkEntityCreate($user, $entity, $data)) {
            return true;
        }

        $campaign = $entity->getCampaign();

        if ($campaign && $this->aclManager->checkEntityEdit($user, $campaign)) {
            return true;
        }

        return false;
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($this->defaultAccessChecker->checkEntityDelete($user, $entity, $data)) {
            return true;
        }

        $campaign = $entity->getCampaign();

        if ($campaign && $this->aclManager->checkEntityEdit($user, $campaign)) {
            return true;
        }

        return false;
    }
}
