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

use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Acl\OwnershipOwnChecker;
use Espo\Core\Acl\OwnershipTeamChecker;

/**
 * @implements OwnershipOwnChecker<User>
 * @implements OwnershipTeamChecker<User>
 */
class OwnershipChecker implements OwnershipOwnChecker, OwnershipTeamChecker
{
    public function checkOwn(User $user, Entity $entity): bool
    {
        return $user->getId() === $entity->getId();
    }

    public function checkTeam(User $user, Entity $entity): bool
    {
        assert($entity instanceof CoreEntity);

        $intersect = array_intersect(
            $user->getLinkMultipleIdList(Field::TEAMS),
            $entity->getLinkMultipleIdList(Field::TEAMS)
        );

        if (count($intersect)) {
            return true;
        }

        return false;
    }
}
