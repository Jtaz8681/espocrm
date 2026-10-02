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

use Espo\Core\Acl\AssignmentChecker\Helper;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Entity;
use Espo\Entities\User;

/**
 * @implements AssignmentChecker<CoreEntity>
 */
class DefaultAssignmentChecker implements AssignmentChecker
{
    protected const FIELD_ASSIGNED_USERS = Field::ASSIGNED_USERS;
    private const FIELD_COLLABORATORS = Field::COLLABORATORS;

    public function __construct(
        private Helper $helper,
    ) {}

    public function check(User $user, Entity $entity): bool
    {
        if (!$this->isPermittedAssignedUser($user, $entity)) {
            return false;
        }

        if (!$this->isPermittedTeams($user, $entity)) {
            return false;
        }

        if ($this->helper->hasAssignedUsersField($entity->getEntityType())) {
            if (!$this->isPermittedAssignedUsers($user, $entity)) {
                return false;
            }
        }

        if ($this->helper->hasCollaboratorsField($entity->getEntityType())) {
            if (!$this->helper->checkUsers($user, $entity, self::FIELD_COLLABORATORS)) {
                return false;
            }
        }

        return true;
    }

    protected function isPermittedAssignedUser(User $user, Entity $entity): bool
    {
        return $this->helper->checkAssignedUser($user, $entity);
    }

    protected function isPermittedTeams(User $user, Entity $entity): bool
    {
        return $this->helper->checkTeams($user, $entity);
    }

    /**
     * Left for backward compatibility.
     */
    protected function isPermittedAssignedUsers(User $user, Entity $entity): bool
    {
        return $this->helper->checkUsers($user, $entity, self::FIELD_ASSIGNED_USERS);
    }
}
