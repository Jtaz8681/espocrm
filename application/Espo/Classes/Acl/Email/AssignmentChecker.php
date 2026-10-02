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

use Espo\Core\Acl\AssignmentChecker as AssignmentCheckerInterface;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements AssignmentCheckerInterface<Email>
 */
class AssignmentChecker implements AssignmentCheckerInterface
{
    public function __construct(
        private AssignmentCheckerInterface\Helper $helper,
    ) {}

    public function check(User $user, Entity $entity): bool
    {
        if ($entity->getAssignedUser() && !$this->helper->checkAssignedUser($user, $entity)) {
            return false;
        }

        if ($entity->getTeams()->getIdList() !== [] && !$this->helper->checkTeams($user, $entity)) {
            return false;
        }

        return true;
    }
}
