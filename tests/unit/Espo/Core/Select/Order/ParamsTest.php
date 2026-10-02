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

namespace tests\unit\Espo\Core\Select\Order;

use Espo\Core\{
    Select\Order\Params,
};

use InvalidArgumentException;

class ParamsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testFromArray()
    {
        $item = Params::fromAssoc([
            'order' => 'DESC',
            'orderBy' => 'test',
            //'forbidComplexExpressions' => true,
            'forceDefault' => true,
        ]);

        $this->assertEquals('DESC', $item->getOrder());
        $this->assertEquals('test', $item->getOrderBy());
        //$this->assertTrue($item->forbidComplexExpressions());
        $this->assertTrue($item->forceDefault());

        $item = Params::fromAssoc([
            //'forbidComplexExpressions' => false,
            'forceDefault' => false,
        ]);

        //$this->assertFalse($item->forbidComplexExpressions());
        $this->assertFalse($item->forceDefault());
    }

    public function testEmpty()
    {
        $item = Params::fromAssoc([
        ]);

        $this->assertEquals(null, $item->getOrder());
        $this->assertEquals(null, $item->getOrderBy());
        //$this->assertEquals(false, $item->forbidComplexExpressions());
        $this->assertEquals(false, $item->forceDefault());
    }

    public function testBadOrder()
    {
        $this->expectException(InvalidArgumentException::class);

        $params = Params::fromAssoc([
            'order' => 'd',
        ]);
    }

    public function testNonExistingParam()
    {
        $this->expectException(InvalidArgumentException::class);

        $params = Params::fromAssoc([
            'bad' => 'd',
        ]);
    }
}
