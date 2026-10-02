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
use Espo\Core\Select\Bool\Applier as BoolFilterListApplier;
use Espo\Core\Select\Bool\Filter as BoolFilter;
use Espo\Core\Select\Bool\FilterFactory as BoolFilterFactory;

use Espo\Entities\User;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class BoolFilterListApplierTest extends TestCase
{
    private $boolFilterFactory;
    private $user;
    private $queryBuilder;
    private $entityType;
    private $applier;

    protected function setUp(): void
    {
        $this->boolFilterFactory = $this->createMock(BoolFilterFactory::class);
        $this->user = $this->createMock(User::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $this->entityType = 'Test';

        $this->applier = new BoolFilterListApplier(
            entityType: $this->entityType,
            user: $this->user,
            boolFilterFactory: $this->boolFilterFactory,
        );
    }

    public function testApply1()
    {
        $boolFilterList = ['test1', 'test2'];

        $filter1 = $this->createFilterMock(['test' => '1']);
        $filter2 = $this->createFilterMock(['test' => '2']);

        $this->initApplierTest($boolFilterList, [$filter1, $filter2], [true, true]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('where');

        $this->applier->apply($this->queryBuilder, $boolFilterList);
    }

    public function testApply2()
    {
        $boolFilterList = ['test1'];

        $filter1 = $this->createFilterMock(['test' => '1']);

        $this->initApplierTest($boolFilterList, [$filter1], [true]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('where');

        $this->applier->apply($this->queryBuilder, $boolFilterList);
    }

    public function testApply3()
    {
        $boolFilterList = ['test1'];

        $this->initApplierTest($boolFilterList, [null], [false]);

        $this->expectException(BadRequest::class);

        $this->applier->apply($this->queryBuilder, $boolFilterList);
    }

    protected function initApplierTest(array $filterNameList, array $filterList, array $hasList)
    {
        $hasMap = [];
        $createMap = [];

        foreach ($filterNameList as $i => $filterName) {
            $hasMap[] = [$this->entityType, $filterName, $hasList[$i]];

            if (!$hasList[$i]) {
                continue;
            }

            $createMap[] = [$this->entityType, $this->user, $filterName, $filterList[$i]];
        }

        $this->boolFilterFactory
            ->expects($this->any())
            ->method('has')
            ->willReturnMap($hasMap);

        $this->boolFilterFactory
            ->expects($this->any())
            ->method('create')
            ->willReturnMap($createMap);
    }

    protected function createFilterMock(array $rawWhereClause): BoolFilter
    {
        $filter = $this->createMock(BoolFilter::class);

        $whereClause = $this->createMock(WhereClause::class);

        $whereClause
            ->expects($this->any())
            ->method('getRawValue')
            ->willReturn($rawWhereClause);

        $filter
            ->expects($this->any())
            ->method('apply')
            ->with($this->queryBuilder, $this->isInstanceOf(OrGroupBuilder::class));

        return $filter;
    }
}
