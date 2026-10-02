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

namespace Espo\Classes\Acl\CurrencyRecordRate;

use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Entities\CurrencyRecordRate;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements AccessEntityCREDChecker<CurrencyRecordRate>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(
        private DefaultAccessChecker $defaultAccessChecker,
    ) {}

    public function checkCreate(User $user, ScopeData $data): bool
    {
        if ($data->getEdit() === Table::LEVEL_YES) {
            return true;
        }

        return false;
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        if ($data->getEdit() === Table::LEVEL_YES) {
            return true;
        }

        return false;
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkCreate($user, $data);
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkDelete($user, $data);
    }
}
