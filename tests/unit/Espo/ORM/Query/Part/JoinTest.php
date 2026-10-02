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

use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Join;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\SelectBuilder;

class JoinTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate1(): void
    {
        $conditions = Expr::isNull(
            Expr::column('test')
        );

        $join = Join::createWithRelationTarget('test')
            ->withAlias('testAlias')
            ->withConditions($conditions);

        $this->assertEquals('test', $join->getTarget());
        $this->assertEquals('testAlias', $join->getAlias());
        $this->assertEquals($conditions, $join->getConditions());

        $this->assertTrue($join->isRelation());
        $this->assertFalse($join->isTable());

        $this->assertEquals(Join::MODE_RELATION, $join->getMode());
    }

    public function testCreate2(): void
    {
        $conditions = Expr::isNull(
            Expr::column('test')
        );

        $join = Join::createWithTableTarget('Test')
            ->withAlias('testAlias')
            ->withConditions($conditions);

        $this->assertEquals('Test', $join->getTarget());
        $this->assertEquals('testAlias', $join->getAlias());
        $this->assertEquals($conditions, $join->getConditions());

        $this->assertTrue($join->isTable());
        $this->assertFalse($join->isRelation());

        $this->assertEquals(Join::MODE_TABLE, $join->getMode());
    }

    public function testCreate3(): void
    {
        $join = Join::create('Test', 'testAlias');

        $this->assertEquals('Test', $join->getTarget());
        $this->assertEquals('testAlias', $join->getAlias());
    }

    public function testCreate4(): void
    {
        $join = Join::createWithSubQuery(
            SelectBuilder::create()
                ->select(Expression::value(true))
                ->build()
            ,
            'a'
        );

        $this->assertTrue($join->isSubQuery());
        $this->assertEquals(Join::MODE_SUB_QUERY, $join->getMode());
    }
}
