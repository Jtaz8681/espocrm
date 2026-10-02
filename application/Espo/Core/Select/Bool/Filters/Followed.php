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

namespace Espo\Core\Select\Bool\Filters;

use Espo\Core\Select\Bool\Filter;
use Espo\Entities\StreamSubscription;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\SelectBuilder;

class Followed implements Filter
{
    public function __construct(private string $entityType, private User $user)
    {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $alias = 'subscriptionFollowedBoolFilter';

        $queryBuilder->leftJoin(
            StreamSubscription::ENTITY_TYPE,
            $alias,
            [
                $alias . '.entityType' => $this->entityType,
                $alias . '.entityId=:' => Attribute::ID,
                $alias . '.userId' => $this->user->getId(),
            ]
        );

        $orGroupBuilder->add(
            WhereClause::fromRaw([
                $alias . '.id!=' => null,
            ])
        );
    }
}
