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

namespace Espo\Classes\Select\GroupEmailFolder\AccessControlFilters;

use Espo\Core\Name\Field;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Core\Select\Helpers\RelationQueryHelper;
use Espo\Entities\GroupEmailFolder;
use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

/**
 * @noinspection PhpUnused
 */
class OnlyTeam implements Filter
{
    public function __construct(
        private User $user,
        private RelationQueryHelper $relationQueryHelper,
    ) {}

    public function apply(QueryBuilder $queryBuilder): void
    {
        $where = $this->relationQueryHelper
            ->prepareLinkWhereMany(GroupEmailFolder::ENTITY_TYPE, Field::TEAMS, $this->user->getTeamIdList());

        $queryBuilder->where($where);
    }
}
