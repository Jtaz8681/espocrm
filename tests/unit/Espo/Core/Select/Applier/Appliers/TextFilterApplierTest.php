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

use Espo\Core\Select\Text\Applier as TextFilterApplier;
use Espo\Core\Select\Text\ConfigProvider;
use Espo\Core\Select\Text\DefaultFilter;
use Espo\Core\Select\Text\FilterFactory;
use Espo\Core\Select\Text\FilterParams;
use Espo\Core\Select\Text\FullTextSearch\Data as FullTextSearchData;
use Espo\Core\Select\Text\FullTextSearch\DataComposer\Params as FullTextSearchDataComposerParams;
use Espo\Core\Select\Text\FullTextSearch\DataComposerFactory as FullTextSearchDataComposerFactory;
use Espo\Core\Select\Text\FullTextSearch\DefaultDataComposer as FullTextSearchDataComposer;
use Espo\Core\Select\Text\MetadataProvider;
use Espo\Core\Utils\Config;

use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class TextFilterApplierTest extends TestCase
{
    private $metadataProvider;
    private $queryBuilder;
    private $filterParams;
    private $config;
    private $fullTextSearchDataComposerFactory;
    private $filterFactory;
    private $applier;
    private $entityType;

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->filterParams = $this->createMock(FilterParams::class);
        $this->config = $this->createMock(Config::class);
        $this->fullTextSearchDataComposerFactory = $this->createMock(FullTextSearchDataComposerFactory::class);
        $this->filterFactory = $this->createMock(FilterFactory::class);

        $configProvider = $this->createMock(ConfigProvider::class);

        $this->entityType = 'Test';

        $this->applier = new TextFilterApplier(
            $this->entityType,
            $user,
            $this->metadataProvider,
            $this->fullTextSearchDataComposerFactory,
            $this->filterFactory,
            $configProvider
        );
    }

    public function testApply1()
    {
        $this->initTest(false);
    }

    public function testApply2()
    {
        $this->initTest(true);
    }

    public function testApply3()
    {
        $this->initTest(false, '1000');
    }

    protected function initTest(bool $noFullTextSearch = false, ?string $filter = null)
    {
        $filter = $filter ?? 'test';

        $filterParams = $this->createMock(FilterParams::class);

        $filterParams
            ->expects($this->any())
            ->method('noFullTextSearch')
            ->willReturn($noFullTextSearch);

        $this->config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(

                    [
                        ['textFilterUseContainsForVarchar', false],
                        ['textFilterContainsMinLength', 4],
                    ]

            );

        $configProvider = new ConfigProvider($this->config);

        $defaultFilter = new DefaultFilter(
            $this->entityType,
            $this->metadataProvider,
            $configProvider
        );

        $this->filterFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn(
                $defaultFilter
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('getFullTextSearchOrderType')
            ->with($this->entityType)
            ->willReturn(null);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getAttributeType')
            ->willReturnMap(

                    [
                        [$this->entityType, 'fieldVarchar', Entity::VARCHAR],
                        [$this->entityType, 'fieldText', Entity::TEXT],
                        [$this->entityType, 'fieldFullText', Entity::TEXT],
                        [$this->entityType, 'fieldInt', Entity::INT],
                        [$this->entityType, 'fieldForeign', Entity::FOREIGN],
                        ['ForeignEntityType', 'field', Entity::VARCHAR],
                    ]
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('getAttributeRelationParam')
            ->with($this->entityType, 'fieldForeign')
            ->willReturn('link1');

        $this->metadataProvider
            ->expects($this->any())
            ->method('getRelationType')
            ->with($this->entityType, 'link2')
            ->willReturn(Entity::HAS_MANY);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getRelationEntityType')
            ->with($this->entityType, 'link2')
            ->willReturn('ForeignEntityType');

        $this->metadataProvider
            ->expects($this->any())
            ->method('getTextFilterAttributeList')
            ->with($this->entityType)
            ->willReturn(
                ['fieldVarchar', 'fieldText', 'fieldFullText', 'fieldInt', 'fieldForeign', 'link2.field']
            );

        if (!$noFullTextSearch) {
            $this->initFullTextSearchData($filter, ['fieldFullText'], 'TEST:(test)');
        }

        $expectedWhere = [
            ['fieldVarchar*' => $filter . '%'],
            ['fieldText*' => '%' . $filter . '%']
        ];

        if (is_numeric($filter)) {
            $expectedWhere[] = ['fieldInt=' => intval($filter)];
        }

        if ($noFullTextSearch) {
            $expectedWhere[] = ['fieldFullText*' => '%' . $filter . '%'];
        }

        $expectedWhere[] = ['fieldForeign*' => $filter . '%'];
        $expectedWhere[] = ['link2.field*' => $filter . '%'];

        if (!$noFullTextSearch) {
            $expectedWhere[] = ['NOT_EQUAL:(TEST:(test), 0):' => null];
        }

        $this->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with(OrGroup::fromRaw($expectedWhere));

        $c = $this->exactly(2);

        $this->queryBuilder
            ->expects($c)
            ->method('leftJoin')
            ->willReturnCallback(function ($link) use ($c) {
                if ($c->numberOfInvocations() === 1) {
                    $this->assertEquals('link1', $link);
                }

                if ($c->numberOfInvocations() === 2) {
                    $this->assertEquals('link2', $link);
                }

                return $this->queryBuilder;
            });

        $this->queryBuilder
            ->expects($this->once())
            ->method('distinct');

        $this->applier->apply($this->queryBuilder, $filter, $this->filterParams);
    }

    protected function initFullTextSearchData(string $filter, array $fieldList, string $expression)
    {
        $fullTextSearchData = $this->createMock(FullTextSearchData::class);

        $fullTextSearchDataComposer = $this->createMock(FullTextSearchDataComposer::class);

        $fullTextSearchDataComposerParams = FullTextSearchDataComposerParams::create();

        $this->fullTextSearchDataComposerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->entityType)
            ->willReturn($fullTextSearchDataComposer);

        $fullTextSearchData
            ->expects($this->any())
            ->method('getFieldList')
            ->willReturn($fieldList);

        $fullTextSearchData
            ->expects($this->any())
            ->method('getExpression')
            ->willReturn(Expr::create($expression));

        $fullTextSearchDataComposer
            ->expects($this->any())
            ->method('compose')
            ->with($filter, $fullTextSearchDataComposerParams)
            ->willReturn($fullTextSearchData);
    }
}
