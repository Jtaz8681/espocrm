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

use Espo\Core\Select\AccessControl\Applier as AccessControlFilterApplier;
use Espo\Core\Select\AccessControl\Filter as AccessControlFilter;
use Espo\Core\Select\AccessControl\FilterFactory as AccessControlFilterFactory;
use Espo\Core\Select\AccessControl\FilterResolver;
use Espo\Core\Select\AccessControl\FilterResolverFactory as AccessControlFilterResolverFactory;

use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class AccessControlFilterApplierTest extends TestCase
{
    private $filterFactory;
    private $filterResolverFactory;
    private $user;
    private $queryBuilder;
    private $filterResolver;
    private $filter;
    private $mandatoryFilter;
    private $entityType;
    private $applier;

    protected function setUp() : void
    {
        $this->filterFactory = $this->createMock(AccessControlFilterFactory::class);
        $this->filterResolverFactory = $this->createMock(AccessControlFilterResolverFactory::class);
        $this->user = $this->createMock(User::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->filterResolver = $this->createMock(FilterResolver::class);
        $this->filter = $this->createMock(AccessControlFilter::class);
        $this->mandatoryFilter = $this->createMock(AccessControlFilter::class);

        $this->entityType = 'Test';

        $this->applier = new AccessControlFilterApplier(
            entityType: $this->entityType,
            user: $this->user,
            accessControlFilterFactory: $this->filterFactory,
            accessControlFilterResolverFactory: $this->filterResolverFactory,
        );
    }

    public function testApply1()
    {
        $this->initApplierTest(true, true);

        $this->applier->apply($this->queryBuilder);
    }

    public function testApply2()
    {
        $this->initApplierTest(true, false);

        $this->applier->apply($this->queryBuilder);
    }

    public function testApply3()
    {
        $this->initApplierTest(false, false);

        $this->applier->apply($this->queryBuilder);
    }

    protected function initApplierTest(bool $resolve, bool $hasFilter)
    {
        $this->filterResolverFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType, $this->user)
            ->willReturn($this->filterResolver);

        $filterName = null;

        if ($resolve) {
            $filterName = 'test';
        }

        $this->filterResolver
            ->expects($this->once())
            ->method('resolve')
            ->willReturn($filterName);

        if (!$resolve) {
            $this->filterFactory
                ->expects($this->never())
                ->method('has');

            return;
        }

        $this->filterFactory
            ->expects($this->once())
            ->method('has')
            ->with($this->entityType, $filterName)
            ->willReturn($hasFilter);

        if (!$hasFilter) {
            $this->expectException(\RuntimeException::class);

            return;
        }

        $this->filterFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [$this->entityType, $this->user, 'mandatory', $this->mandatoryFilter],
                [$this->entityType, $this->user, $filterName, $this->filter],
            ]);

        $this->filter
            ->expects($this->once())
            ->method('apply')
            ->with($this->queryBuilder);
    }
}
