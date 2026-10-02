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

namespace Espo\Modules\Crm\Business\Distribution\Lead;

use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\EntityManager;

use Espo\Entities\User;
use Espo\Entities\Team;

class LeastBusy
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Team $team
     * @param ?string $targetUserPosition
     * @return ?User
     */
    public function getUser($team, $targetUserPosition = null)
    {
        $where = [
            'isActive' => true,
        ];

        if (!empty($targetUserPosition)) {
            $where['@relation.role'] = $targetUserPosition;
        }

        /**
         * @var \Espo\ORM\Collection<User> $userList
         */
        $userList = $this->entityManager
            ->getRDBRepository(Team::ENTITY_TYPE)
            ->getRelation($team, 'users')
            ->where($where)
            ->order('id')
            ->find();

        if (is_countable($userList) && count($userList) == 0) {
            return null;
        }

        $countHash = [];

        foreach ($userList as $user) {
            $where = [
                'assignedUserId' => $user->getId(),
                'status<>' => [
                    Lead::STATUS_CONVERTED,
                    Lead::STATUS_RECYCLED,
                    Lead::STATUS_DEAD,
                ],
            ];

            $count = $this->entityManager
                ->getRDBRepository(Lead::ENTITY_TYPE)
                ->where($where)
                ->count();

            $countHash[$user->getId()] = $count;
        }

        $foundUserId = false;
        $min = false;

        foreach ($countHash as $userId => $count) {
            if ($min === false) {
                $min = $count;
                $foundUserId = $userId;
            } else {
                if ($count < $min) {
                    $min = $count;
                    $foundUserId = $userId;
                }
            }
        }

        if ($foundUserId !== false) {
            return $this->entityManager->getEntityById(User::ENTITY_TYPE, $foundUserId);
        }

        return null;
    }
}

