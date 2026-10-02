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

use Espo\Core\Select\Order\Item;

use InvalidArgumentException;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate1()
    {
        $item = Item::create('test', 'DESC');

        $this->assertEquals('DESC', $item->getOrder());
        $this->assertEquals('test', $item->getOrderBy());
    }
    public function testNonExistingParam()
    {
        $this->expectException(InvalidArgumentException::class);

        Item::create('test', 'test');
    }
}
