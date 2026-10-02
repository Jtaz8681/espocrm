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

namespace Espo\Core\Acl;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Entities\User;

/**
 * A default implementation for ownership checking.
 *
 * @implements OwnershipOwnChecker<CoreEntity>
 * @implements OwnershipTeamChecker<CoreEntity>
 * @implements OwnershipSharedChecker<CoreEntity>
 */
class DefaultOwnershipChecker implements OwnershipOwnChecker, OwnershipTeamChecker, OwnershipSharedChecker
{
    private const ATTR_CREATED_BY_ID = Field::CREATED_BY . 'Id';
    private const ATTR_ASSIGNED_USER_ID = Field::ASSIGNED_USER . 'Id';
    private const ATTR_ASSIGNED_TEAMS_IDS = Field::TEAMS . 'Ids';
    private const FIELD_TEAMS = Field::TEAMS;
    private const FIELD_ASSIGNED_USERS = Field::ASSIGNED_USERS;
    private const FIELD_COLLABORATORS = Field::COLLABORATORS;

    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($entity instanceof CoreEntity && $entity->hasLinkMultipleField(self::FIELD_ASSIGNED_USERS)) {
            if ($entity->hasLinkMultipleId(self::FIELD_ASSIGNED_USERS, $user->getId())) {
                return true;
            }

            return false;
        }

        if ($entity->hasAttribute(self::ATTR_ASSIGNED_USER_ID)) {
            if (
                $entity->has(self::ATTR_ASSIGNED_USER_ID) &&
                $user->getId() === $entity->get(self::ATTR_ASSIGNED_USER_ID)
            ) {
                return true;
            }

            return false;
        }

        if ($entity->hasAttribute(self::ATTR_CREATED_BY_ID)) {
            if (
                $entity->has(self::ATTR_CREATED_BY_ID) &&
                $user->getId() === $entity->get(self::ATTR_CREATED_BY_ID)
            ) {
                return true;
            }
        }

        return false;
    }

    public function checkTeam(User $user, Entity $entity): bool
    {
        if (!$entity instanceof CoreEntity) {
            return false;
        }

        $userTeamIdList = $user->getLinkMultipleIdList(self::FIELD_TEAMS);

        if (
            !$entity->hasRelation(self::FIELD_TEAMS) ||
            !$entity->hasAttribute(self::ATTR_ASSIGNED_TEAMS_IDS)
        ) {
            return false;
        }

        $entityTeamIdList = $entity->getLinkMultipleIdList(self::FIELD_TEAMS);

        if (empty($entityTeamIdList)) {
            return false;
        }

        foreach ($userTeamIdList as $id) {
            if (in_array($id, $entityTeamIdList)) {
                return true;
            }
        }

        return false;
    }

    public function checkShared(User $user, Entity $entity, string $action): bool
    {
        if (!$entity instanceof CoreEntity) {
            return false;
        }

        if ($action !== Table::ACTION_READ && $action !== Table::ACTION_STREAM) {
            return false;
        }

        if (
            !$entity->hasRelation(self::FIELD_COLLABORATORS) ||
            !$entity->hasLinkMultipleField(self::FIELD_COLLABORATORS)
        ) {
            return false;
        }

        return in_array($user->getId(), $entity->getLinkMultipleIdList(self::FIELD_COLLABORATORS));
    }
}
