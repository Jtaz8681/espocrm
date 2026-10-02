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

namespace Espo\Core\Select\Primary\Filters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

/**
 * A dummy filter 'all'. Can be detected in a custom AdditionalApplier to instruct that the filter needs to be
 * bypassed. Use this filter for special cases from code. Users can pass this filter too. Do not rely on this filter
 * when dealing with access control logic.
 *
 * @since 9.2.0
 */
class All implements Filter
{
    public const NAME = 'all';

    public function apply(QueryBuilder $queryBuilder): void
    {}
}
