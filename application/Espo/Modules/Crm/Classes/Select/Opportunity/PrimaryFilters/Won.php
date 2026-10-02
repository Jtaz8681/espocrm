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

namespace Espo\Modules\Crm\Classes\Select\Opportunity\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Condition as Cond;

use Espo\Modules\Crm\Classes\Select\Opportunity\Utils\StageListProvider;

class Won implements Filter
{
    private $stageListPoriver;

    public function __construct(StageListProvider $stageListPoriver)
    {
        $this->stageListPoriver = $stageListPoriver;
    }

    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(
            Cond::in(
                Cond::column('stage'),
                array_merge(
                    $this->stageListPoriver->getWon(),
                )
            )
        );
    }
}
