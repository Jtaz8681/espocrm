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

namespace Espo\Classes\Select\Email\AccessControlFilters;

use Espo\Core\Select\AccessControl\Filter;

use Espo\Entities\Email;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class OnlyTeam implements Filter
{
    public function __construct(private User $user)
    {}

    public function apply(QueryBuilder $queryBuilder): void
    {
        $subQuery = QueryBuilder::create()
            ->select(Attribute::ID)
            ->from(Email::ENTITY_TYPE)
            ->leftJoin(Team::RELATIONSHIP_ENTITY_TEAM, 'entityTeam', [
                'entityTeam.entityId:' => 'id',
                'entityTeam.entityType' => Email::ENTITY_TYPE,
                'entityTeam.deleted' => false,
            ])
            ->leftJoin(Email::RELATIONSHIP_EMAIL_USER, Email::ALIAS_INBOX, [
                Email::ALIAS_INBOX . '.emailId:' => 'id',
                Email::ALIAS_INBOX . '.deleted' => false,
                Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
            ])
            ->where([
                'OR' => [
                    'entityTeam.teamId' => $this->user->getTeamIdList(),
                    Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
                ]
            ])
            ->build();

        $queryBuilder->where(
            Cond::in(
                Cond::column(Attribute::ID),
                $subQuery
            )
        );
    }
}
