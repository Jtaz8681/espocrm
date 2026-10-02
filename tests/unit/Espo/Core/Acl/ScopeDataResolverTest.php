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

namespace tests\unit\Espo\Core\Acl;

use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;
use PHPUnit\Framework\TestCase;

class ScopeDataResolverTest extends TestCase
{
    public function testResolve1(): void
    {
        $table = $this->createMock(Table::class);
        $resolver = new Table\ScopeDataResolver($table);

        $table->expects($this->once())
            ->method('getScopeData')
            ->with('Test')
            ->willReturn(ScopeData::fromRaw(true));

        $this->assertTrue($resolver->resolve('Test')->isTrue());
    }

    public function testResolve2(): void
    {
        $table = $this->createMock(Table::class);
        $resolver = new Table\ScopeDataResolver($table);

        $table->expects($this->once())
            ->method('getScopeData')
            ->willReturnMap([
                ['Test', ScopeData::fromRaw(false)]
            ]);

        $this->assertTrue($resolver->resolve('Test')->isFalse());
    }

    public function testResolve3(): void
    {
        $table = $this->createMock(Table::class);
        $resolver = new Table\ScopeDataResolver($table);

        $table->expects($this->once())
            ->method('getScopeData')
            ->with('Test')
            ->willReturn(ScopeData::fromRaw((object) ['create' => 'yes', 'edit' => 'no']));

        $result = $resolver->resolve('Test');

        $this->assertEquals('yes', $result->getCreate());
        $this->assertEquals('no', $result->getEdit());
    }

    public function testResolve4(): void
    {
        $table = $this->createMock(Table::class);
        $resolver = new Table\ScopeDataResolver($table);

        $table->expects($this->once())
            ->method('getScopeData')
            ->with('Test')
            ->willReturn(ScopeData::fromRaw((object) ['create' => 'yes', 'edit' => 'no']));

        $this->assertTrue($resolver->resolve('boolean:Test')->isTrue());
    }

    public function testResolve5(): void
    {
        $table = $this->createMock(Table::class);
        $resolver = new Table\ScopeDataResolver($table);

        $table->expects($this->once())
            ->method('getScopeData')
            ->with('Test')
            ->willReturn(ScopeData::fromRaw((object) ['create' => 'no', 'edit' => 'no']));

        $this->assertTrue($resolver->resolve('boolean:Test')->isTrue());
    }
}
