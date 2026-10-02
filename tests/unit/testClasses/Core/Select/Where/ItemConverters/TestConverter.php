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

namespace tests\unit\testClasses\Core\Select\Where\ItemConverters;

use Espo\{
    ORM\Query\SelectBuilder as QueryBuilder,
    ORM\Query\Part\WhereClause,
    ORM\Query\Part\WhereItem as WhereClauseItem,
    Core\Select\Where\Item,
    Core\Select\Where\ItemConverter,
};

class TestConverter implements ItemConverter
{
    public function convert(QueryBuilder $queryBuilder, Item $item) : WhereClauseItem
    {
        return WhereClause::fromRaw([]);
    }
}
