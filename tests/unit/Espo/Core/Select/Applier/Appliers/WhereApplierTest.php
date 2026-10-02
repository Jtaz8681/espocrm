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

namespace tests\unit\Espo\Core\Select\Applier\Appliers;

use Espo\Core\Select\Where\Applier as WhereApplier;
use Espo\Core\Select\Where\Checker;
use Espo\Core\Select\Where\CheckerFactory;
use Espo\Core\Select\Where\Converter;
use Espo\Core\Select\Where\ConverterFactory;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Core\Select\Where\Params;

use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class WhereApplierTest extends TestCase
{
    private $user;
    private $queryBuilder;
    private $whereItem;
    private $converterFactory;
    private $converter;
    private $checkerFactory;
    private $checker;
    private $params;
    private $entityType;
    private $applier;

    protected function setUp(): void
    {
        $this->user = $this->createMock(User::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->whereItem = $this->createMock(WhereItem::class);
        $this->converterFactory = $this->createMock(ConverterFactory::class);
        $this->converter = $this->createMock(Converter::class);
        $this->checkerFactory = $this->createMock(CheckerFactory::class);
        $this->checker = $this->createMock(Checker::class);
        $this->params = $this->createMock(Params::class);

        $this->entityType = 'Test';

        $this->applier = new WhereApplier(
            $this->entityType,
            $this->user,
            $this->converterFactory,
            $this->checkerFactory
        );
    }

    public function testApply1()
    {
        $this->checker
            ->expects($this->once())
            ->method('check')
            ->with($this->whereItem, $this->params);

        $this->checkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user)
            ->willReturn($this->checker);

        $this->converter
            ->expects($this->once())
            ->method('convert')
            ->with($this->queryBuilder, $this->whereItem);

        $this->converterFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user)
            ->willReturn($this->converter);

        $this->applier->apply($this->queryBuilder, $this->whereItem, $this->params);
    }

    public function testApply2()
    {
        $this->checker
            ->expects($this->once())
            ->method('check')
            ->with($this->whereItem, $this->params);

        $this->checkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user)
            ->willReturn($this->checker);

        $this->converter
            ->expects($this->once())
            ->method('convert')
            ->with($this->queryBuilder, $this->whereItem);

        $this->converterFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user)
            ->willReturn($this->converter);

        $this->applier->apply($this->queryBuilder, $this->whereItem, $this->params);
    }
}
