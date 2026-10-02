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

namespace Espo\Classes\Acl\WorkingTimeRange;

use Espo\Core\Acl\AssignmentChecker as AssignmentCheckerInterface;
use Espo\Core\Acl\DefaultAssignmentChecker;
use Espo\Core\AclManager;
use Espo\Entities\User;
use Espo\Entities\WorkingTimeRange;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @implements AssignmentCheckerInterface<WorkingTimeRange>
 */
class AssignmentChecker implements AssignmentCheckerInterface
{
    private DefaultAssignmentChecker $defaultAssignmentChecker;
    private AclManager $aclManager;
    private EntityManager $entityManager;

    public function __construct(
        DefaultAssignmentChecker $defaultAssignmentChecker,
        AclManager $aclManager,
        EntityManager $entityManager
    ) {
        $this->defaultAssignmentChecker = $defaultAssignmentChecker;
        $this->aclManager = $aclManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param WorkingTimeRange $entity
     */
    public function check(User $user, Entity $entity): bool
    {
        $result = $this->defaultAssignmentChecker->check($user, $entity);

        if (!$result) {
            return false;
        }

        if (!$entity->isAttributeChanged('usersIds')) {
            return true;
        }

        $users = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([Attribute::ID => $entity->getUsers()->getIdList()])
            ->find();

        foreach ($users as $targetUser) {
            $accessToUser = $this->aclManager->check($user, $targetUser);

            if (!$accessToUser) {
                return false;
            }
        }

        return true;
    }
}
