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

namespace Espo\Modules\Crm\Tools\Case\Distribution;

use Espo\Entities\User;
use Espo\Entities\Team;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\EntityManager;

class RoundRobin
{
    public function __construct(private EntityManager $entityManager)
    {}

    public function getUser(Team $team, ?string $targetUserPosition = null): ?User
    {
        $where = [
            'isActive' => true,
        ];

        if (!empty($targetUserPosition)) {
            $where['@relation.role'] = $targetUserPosition;
        }

        $userList = $this->entityManager
            ->getRDBRepositoryByClass(Team::class)
            ->getRelation($team, 'users')
            ->where($where)
            ->order('id')
            ->find();

        if (count($userList) === 0) {
            return null;
        }

        $userIdList = [];

        foreach ($userList as $user) {
            $userIdList[] = $user->getId();
        }

        /** @var ?CaseObj $case */
        $case = $this->entityManager
            ->getRDBRepository(CaseObj::ENTITY_TYPE)
            ->where([
                'assignedUserId' => $userIdList,
            ])
            ->order('number', 'DESC')
            ->findOne();

        if (empty($case)) {
            $num = 0;
        } else {
            $num = array_search($case->getAssignedUser()?->getId(), $userIdList);

            if ($num === false || $num == count($userIdList) - 1) {
                $num = 0;
            } else {
                $num++;
            }
        }

        $id = $userIdList[$num];

        /** @var User */
        return $this->entityManager->getEntityById(User::ENTITY_TYPE, $id);
    }
}
