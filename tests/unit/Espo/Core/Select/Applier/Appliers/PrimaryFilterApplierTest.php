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

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Select\Primary\Applier as PrimaryFilterApplier;
use Espo\Core\Select\Primary\Filter as PrimaryFilter;
use Espo\Core\Select\Primary\FilterFactory as PrimaryFilterFactory;

use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class PrimaryFilterApplierTest extends TestCase
{
    private $filterFactory;
    private $user;
    private $queryBuilder;
    private $entityType;
    private $applier;

    protected function setUp(): void
    {
        $this->filterFactory = $this->createMock(PrimaryFilterFactory::class);
        $this->user = $this->createMock(User::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $this->entityType = 'Test';

        $this->applier = new PrimaryFilterApplier(
            entityType: $this->entityType,
            user: $this->user,
            primaryFilterFactory: $this->filterFactory,
        );
    }

    public function testApply1()
    {
        $filterName = 'test';

        $filter = $this->createMock(PrimaryFilter::class);

        $filter
            ->expects($this->once())
            ->method('apply')
            ->with($this->queryBuilder);

        $this->filterFactory
            ->expects($this->once())
            ->method('has')
            ->with($this->entityType, $filterName)
            ->willReturn(true);

        $this->filterFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user, $filterName)
            ->willReturn($filter);

        $this->applier->apply($this->queryBuilder, $filterName);
    }

    public function testApply2()
    {
        $filterName = 'test';

        $this->filterFactory
            ->expects($this->once())
            ->method('has')
            ->with($this->entityType, $filterName)
            ->willReturn(false);

        $this->filterFactory
            ->expects($this->never())
            ->method('create');

        $this->expectException(BadRequest::class);

        $this->applier->apply($this->queryBuilder, $filterName);
    }
}
