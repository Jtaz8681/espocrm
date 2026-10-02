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

namespace Espo\Modules\Crm\Classes\Acl\Meeting;

use Espo\Core\Acl;
use Espo\Core\Acl\AssignmentChecker as AssignmentCheckerInterface;
use Espo\Core\Acl\DefaultAssignmentChecker;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @implements AssignmentCheckerInterface<Meeting|Call>
 */
class AssignmentChecker implements AssignmentCheckerInterface
{
    public function __construct(
        private DefaultAssignmentChecker $defaultAssignmentChecker,
        private EntityManager $entityManager,
        private Acl $acl
    ) {}

    public function check(User $user, Entity $entity): bool
    {
        if (!$this->defaultAssignmentChecker->check($user, $entity)) {
            return false;
        }

        $userIds = $this->getUserIds($entity);

        foreach ($userIds as $userId) {
            if (!$this->acl->checkAssignmentPermission($userId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getUserIds(Meeting|Call $entity): array
    {
        $userIdList = $entity->getUsers()->getIdList();

        if ($entity->isNew()) {
            return $userIdList;
        }

        $newIdList = [];
        $existingIdList = [];

        $usersCollection = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, 'users')
            ->select(Attribute::ID)
            ->find();

        foreach ($usersCollection as $user) {
            $existingIdList[] = $user->getId();
        }

        foreach ($userIdList as $id) {
            if (!in_array($id, $existingIdList)) {
                $newIdList[] = $id;
            }
        }

        return $newIdList;
    }
}
