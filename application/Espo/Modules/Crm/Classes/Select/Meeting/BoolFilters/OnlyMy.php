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

namespace Espo\Modules\Crm\Classes\Select\Meeting\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Condition as Cond;

use Espo\Entities\User;

class OnlyMy implements Filter
{
    public function __construct(private User $user)
    {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $queryBuilder
            ->leftJoin('users', 'usersFilterOnlyMy');

        $orGroupBuilder
            ->add(
                Cond::and(
                    Cond::equal(
                        Cond::column('usersFilterOnlyMyMiddle.userId'),
                        $this->user->getId()
                    ),
                    Cond::or(
                        Cond::notEqual(
                            Cond::column('usersFilterOnlyMyMiddle.status'),
                            Meeting::ATTENDEE_STATUS_DECLINED
                        ),
                        Cond::equal(
                            Cond::column('usersFilterOnlyMyMiddle.status'),
                            null
                        )
                    )
                )
            );
    }
}
