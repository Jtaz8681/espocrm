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

namespace tests\unit\Espo\ORM\Query\Part;

use Espo\ORM\Query\Part\Order as OrderExpr;
use Espo\ORM\Query\Part\Expression as Expr;

class OrderExpressionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate1(): void
    {
        $order = OrderExpr::fromString('test')->withDesc();

        $this->assertEquals(Expr::create('test'), $order->getExpression());
        $this->assertEquals(OrderExpr::DESC, $order->getDirection());
        $this->assertEquals(true, $order->isDesc());
    }

    public function testCreate2(): void
    {
        $order = OrderExpr::fromString('test');

        $this->assertEquals(OrderExpr::ASC, $order->getDirection());
        $this->assertEquals(false, $order->isDesc());
    }

    public function testCreate3(): void
    {
        $order = OrderExpr::fromString('test')->withAsc();

        $this->assertEquals(Expr::create('test'), $order->getExpression());
        $this->assertEquals(OrderExpr::ASC, $order->getDirection());
        $this->assertEquals(false, $order->isDesc());
    }

    public function testCreate4(): void
    {
        $order = OrderExpr::fromString('test')->withDirection(OrderExpr::DESC);

        $this->assertEquals(OrderExpr::DESC, $order->getDirection());
    }

    public function testReverseOrder(): void
    {
        $order = OrderExpr::fromString('test')
            ->withDirection(OrderExpr::DESC)
            ->withReverseDirection();

        $this->assertEquals(OrderExpr::ASC, $order->getDirection());
    }
}
