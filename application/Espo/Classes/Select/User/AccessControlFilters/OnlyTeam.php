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
use Espo\Entities\Team;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Entities\User;

/**
 * @noinspection PhpUnused
 */
class OnlyTeam implements Filter
{
    public function __construct(
        private User $user,
        private AclManager $aclManager
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $orGroupBuilder = OrGroup::createBuilder();

        $orGroupBuilder
            ->add(
                Condition::equal(
                    Expression::column('id'),
                    $this->user->getId()
                )
            )
            ->add(
                Condition::in(
                    Expression::column('id'),
                    SelectBuilder::create()
                        ->from(Team::RELATIONSHIP_TEAM_USER)
                        ->select('userId')
                        ->where(['teamId' => $this->user->getTeamIdList()])
                        ->build()
                )
            );


        if ($this->aclManager->getPermissionLevel($this->user, Permission::PORTAL) === Table::LEVEL_YES) {
            $orGroupBuilder->add(
                Condition::equal(
                    Expression::column('type'),
                    User::TYPE_PORTAL
                )
            );
        }

        $queryBuilder->where($orGroupBuilder->build());
    }
}
