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

namespace Espo\Classes\Select\User\AccessControlFilters;

use Espo\Core\Acl\Permission;
use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Select\AccessControl\Filter;

use Espo\Entities\User;

class OnlyOwn implements Filter
{
    public function __construct(private User $user, private AclManager $aclManager)
    {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        if ($this->aclManager->getPermissionLevel($this->user, Permission::PORTAL) === Table::LEVEL_YES) {
            $queryBuilder->where([
                'OR' => [
                    'id' => $this->user->getId(),
                    'type' => User::TYPE_PORTAL,
                ],
            ]);

            return;
        }

        $queryBuilder->where([
            'id' => $this->user->getId(),
        ]);
    }
}
