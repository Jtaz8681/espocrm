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

use Espo\Core\Name\Field;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\EntityManager;

use Espo\Entities\User;
use Espo\Entities\Team;

class RoundRobin
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
     * @return User|null
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

        $userIdList = [];

        foreach ($userList as $user) {
            $userIdList[] = $user->getId();
        }

        $lead = $this->entityManager
            ->getRDBRepository(Lead::ENTITY_TYPE)
            ->where([
                'assignedUserId' => $userIdList
            ])
            ->order(Field::CREATED_AT, 'DESC')
            ->findOne();

        if (empty($lead)) {
            $num = 0;
        } else {
            $num = array_search($lead->get('assignedUserId'), $userIdList);

            if ($num === false || $num == count($userIdList) - 1) {
                $num = 0;
            } else {
                $num++;
            }
        }

        return $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userIdList[$num]);
    }
}
