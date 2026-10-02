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

namespace Espo\Core\Acl\Table;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Entities\Team;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Entities\Role as RoleEntity;

class DefaultRoleListProvider implements RoleListProvider
{
    private const PARAM_BASELINE_ROLE_ID = 'baselineRoleId';

    public function __construct(
        private User $user,
        private EntityManager $entityManager,
        private Config $config,
    ) {}

    /**
     * @return Role[]
     */
    public function get(): array
    {
        $roleList = [];

        $baselineRole = $this->getBaselineRole();

        if ($baselineRole) {
            $roleList[] = $baselineRole;
        }

        /** @var iterable<RoleEntity> $userRoleList */
        $userRoleList = $this->entityManager
            ->getRelation($this->user, User::LINK_ROLES)
            ->find();

        foreach ($userRoleList as $role) {
            $roleList[] = $role;
        }

        /** @var iterable<Team> $teamList */
        $teamList = $this->entityManager
            ->getRelation($this->user, Field::TEAMS)
            ->find();

        foreach ($teamList as $team) {
            /** @var iterable<RoleEntity> $teamRoleList */
            $teamRoleList = $this->entityManager
                ->getRelation($team, Team::LINK_ROLES)
                ->find();

            foreach ($teamRoleList as $role) {
                $roleList[] = $role;
            }
        }

        return array_map(
            fn (RoleEntity $role) => new RoleEntityWrapper($role),
            $roleList
        );
    }

    private function getBaselineRole(): ?RoleEntity
    {
        $roleId = $this->config->get(self::PARAM_BASELINE_ROLE_ID);

        if (!$roleId) {
            return null;
        }

        return $this->entityManager->getRDBRepositoryByClass(RoleEntity::class)->getById($roleId);
    }
}
