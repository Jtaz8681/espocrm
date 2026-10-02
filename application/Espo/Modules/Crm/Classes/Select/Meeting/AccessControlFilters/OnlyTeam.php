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

namespace Espo\Modules\Crm\Classes\Select\Meeting\AccessControlFilters;

use Espo\Core\Select\AccessControl\Filter;
use Espo\ORM\Defs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Condition as Cond;

use Espo\Entities\User;

class OnlyTeam implements Filter
{
    public function __construct(
        private User $user,
        private string $entityType,
        private Defs $defs
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $relationDefs = $this->defs
            ->getEntity($this->entityType)
            ->getRelation('users');

        $middleEntityType = ucfirst($relationDefs->getRelationshipName());
        $key1 = $relationDefs->getMidKey();

        $queryBuilder->where(
            Cond::in(
                Cond::column(Attribute::ID),
                SelectBuilder::create()
                    ->select(Attribute::ID)
                    ->from($this->entityType)
                    ->leftJoin('EntityTeam', 'entityTeam', [
                        'entityTeam.entityId:' => 'id',
                        'entityTeam.entityType' => $this->entityType,
                        'entityTeam.deleted' => false,
                    ])
                    ->leftJoin($middleEntityType, 'usersMiddle', [
                        "usersMiddle.{$key1}:" => 'id',
                        'usersMiddle.deleted' => false,
                    ])
                    ->where(
                        Cond::or(
                            Cond::in(
                                Cond::column('entityTeam.teamId'),
                                $this->user->getTeamIdList(),
                            ),
                            Cond::equal(
                                Cond::column('usersMiddle.userId'),
                                $this->user->getId()
                            ),
                            Cond::equal(
                                Cond::column('assignedUserId'),
                                $this->user->getId()
                            )
                        )
                    )
                    ->build()
            )
        );
    }
}
