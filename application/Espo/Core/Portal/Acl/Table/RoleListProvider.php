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

namespace Espo\Core\Portal\Acl\Table;

use Espo\ORM\EntityManager;

use Espo\Entities\Portal;
use Espo\Entities\PortalRole;
use Espo\Entities\User;
use Espo\Core\Acl\Table\Role;
use Espo\Core\Acl\Table\RoleEntityWrapper;
use Espo\Core\Acl\Table\RoleListProvider as RoleListProviderInterface;

class RoleListProvider implements RoleListProviderInterface
{
    public function __construct(
        private User $user,
        private Portal $portal,
        private EntityManager $entityManager
    ) {}

    /**
     * @return Role[]
     */
    public function get(): array
    {
        $roleList = [];

        /** @var iterable<PortalRole> $userRoleList */
        $userRoleList = $this->entityManager
            ->getRDBRepository(User::ENTITY_TYPE)
            ->getRelation($this->user, 'portalRoles')
            ->find();

        foreach ($userRoleList as $role) {
            $roleList[] = $role;
        }

        /** @var iterable<PortalRole> $portalRoleList */
        $portalRoleList = $this->entityManager
            ->getRDBRepository(Portal::ENTITY_TYPE)
            ->getRelation($this->portal, 'portalRoles')
            ->find();

        foreach ($portalRoleList as $role) {
            $roleList[] = $role;
        }

        return array_map(
            function (PortalRole $role): RoleEntityWrapper {
                return new RoleEntityWrapper($role);
            },
            $roleList
        );
    }
}
