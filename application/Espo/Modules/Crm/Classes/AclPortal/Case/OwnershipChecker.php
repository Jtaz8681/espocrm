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

namespace Espo\Modules\Crm\Classes\AclPortal\Case;

use Espo\Core\Acl\OwnershipOwnChecker;
use Espo\Core\Portal\Acl\DefaultOwnershipChecker;
use Espo\Core\Portal\Acl\OwnershipAccountChecker;
use Espo\Core\Portal\Acl\OwnershipContactChecker;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\Entity;

/**
 * @implements OwnershipAccountChecker<CaseObj>
 * @implements OwnershipOwnChecker<CaseObj>
 * @implements OwnershipContactChecker<CaseObj>
 */
class OwnershipChecker implements OwnershipOwnChecker, OwnershipAccountChecker, OwnershipContactChecker
{
    public function __construct(private DefaultOwnershipChecker $defaultOwnershipChecker) {}

    public function checkAccount(User $user, Entity $entity): bool
    {
        if ($entity->isInternal()) {
            return false;
        }

        return $this->defaultOwnershipChecker->checkAccount($user, $entity);
    }

    public function checkContact(User $user, Entity $entity): bool
    {
        if ($entity->isInternal()) {
            return false;
        }

        return $this->defaultOwnershipChecker->checkContact($user, $entity);
    }

    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($entity->isInternal()) {
            return false;
        }

        return $this->defaultOwnershipChecker->checkOwn($user, $entity);
    }
}
