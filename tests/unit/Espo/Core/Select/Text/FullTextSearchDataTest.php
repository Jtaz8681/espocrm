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

namespace tests\unit\Espo\Core\Select\Text;

use Espo\ORM\Query\Part\Expression as Expr;
use Espo\Core\Select\Text\FullTextSearch\Data;
use Espo\Core\Select\Text\FullTextSearch\Mode;

class FullTextSearchDataTest extends \PHPUnit\Framework\TestCase
{
    public function testGet1(): void
    {
        $item = new Data(Expr::create('TEST:()'), ['test'], ['column'], Mode::BOOLEAN);

        $this->assertEquals('TEST:()', $item->getExpression()->getValue());
        $this->assertEquals(['test'], $item->getFieldList());
        $this->assertEquals(['column'], $item->getColumnList());
        $this->assertEquals(Mode::BOOLEAN, $item->getMode());
    }
}
