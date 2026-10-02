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

namespace Espo\Classes\Acl\Email;

use Espo\Entities\User;
use Espo\Entities\Email;
use Espo\ORM\Entity;
use Espo\Core\Acl\DefaultOwnershipChecker;
use Espo\Core\Acl\OwnershipOwnChecker;
use Espo\Core\Acl\OwnershipTeamChecker;

/**
 * @implements OwnershipOwnChecker<Email>
 * @implements OwnershipTeamChecker<Email>
 */
class OwnershipChecker implements OwnershipOwnChecker, OwnershipTeamChecker
{
    public function __construct(private DefaultOwnershipChecker $defaultOwnershipChecker)
    {}

    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($user->getId() === $entity->getAssignedUser()?->getId()) {
            return true;
        }

        if ($user->getId() === $entity->getCreatedBy()?->getId()) {
            return true;
        }

        if ($entity->getAssignedUsers()->hasId($user->getId())) {
            return true;
        }

        return false;
    }

    public function checkTeam(User $user, Entity $entity): bool
    {
        return $this->defaultOwnershipChecker->checkTeam($user, $entity);
    }
}
