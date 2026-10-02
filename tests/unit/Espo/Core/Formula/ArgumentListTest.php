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

namespace tests\unit\Espo\Core\Formula;

use Espo\Core\Formula\ArgumentList;

class ArgumentListTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    protected function tearDown() : void
    {
    }

    public function testCount()
    {
        $list = new ArgumentList([]);
        $this->assertEquals(0, count($list));

        $list = new ArgumentList([
            null,
            '1',
            (object) [],
        ]);
        $this->assertEquals(3, count($list));
    }

    public function testAccess()
    {
        $list = new ArgumentList([
            null,
            '1',
            (object) [],
        ]);
        $this->assertEquals(null, $list[0]->getData());
        $this->assertEquals('1', $list[1]->getData());
    }

    public function testIteration()
    {
        $list = new ArgumentList([
            null,
            '1',
        ]);

        $array = [];
        foreach ($list as $item) {
            $array[] = $item->getData();
        }
        $this->assertEquals(null, $array[0]);
        $this->assertEquals('1', $array[1]);
    }
}
