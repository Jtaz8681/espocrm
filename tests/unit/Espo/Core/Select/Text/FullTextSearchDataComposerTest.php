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

namespace tests\unit\Espo\Core\Select\Text;

use Espo\Core\Select\Text\FullTextSearch\DataComposer\Params as FullTextSearchDataComposerParams;
use Espo\Core\Select\Text\FullTextSearch\DefaultDataComposer as FullTextSearchDataComposer;
use Espo\Core\Select\Text\MetadataProvider;
use Espo\Core\Utils\Config;
use PHPUnit\Framework\TestCase;

class FullTextSearchDataComposerTest extends TestCase
{
    private $config;
    private $metadataProvider;
    private $entityType;
    private $fullTextSearchDataComposer;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->metadataProvider = $this->createMock(MetadataProvider::class);

        $this->entityType = 'Test';

        $this->fullTextSearchDataComposer = new FullTextSearchDataComposer(
            $this->entityType,
            $this->config,
            $this->metadataProvider
        );
    }

    public function testCompose1()
    {
        $filter = 'test filter';

        $this->config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(

                    [
                        ['fullTextSearchDisabled', false],
                        ['fullTextSearchMinLength', 4],
                    ]
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('isFieldNotStorable')
            ->willReturnMap(

                    [
                        [$this->entityType, 'field1', false],
                        [$this->entityType, 'field2', false],
                        [$this->entityType, 'field3', false],
                    ]
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('isFullTextSearchSupportedForField')
            ->willReturnMap(

                    [
                        [$this->entityType, 'field1', true],
                        [$this->entityType, 'field2', true],
                        [$this->entityType, 'field3', false],
                    ]
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('hasFullTextSearch')
            ->with($this->entityType)
            ->willReturn(true);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getFullTextSearchColumnList')
            ->with($this->entityType)
            ->willReturn(
                ['field1A', 'field1B', 'field2']
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('getTextFilterAttributeList')
            ->with($this->entityType)
            ->willReturn(
                ['field1', 'field2', 'field3']
            );

        $params = FullTextSearchDataComposerParams::create();

        $data = $this->fullTextSearchDataComposer->compose($filter, $params);

        $this->assertNotEquals(null, $data);

        $this->assertEquals(['field1A', 'field1B', 'field2'], $data->getColumnList());
        $this->assertEquals(['field1', 'field2'], $data->getFieldList());

        $this->assertEquals(
            'MATCH_BOOLEAN:(field1A, field1B, field2, \'test filter\')',
            $data->getExpression()->getValue()
        );
    }

    public function testCompose2()
    {
        $filter = 'bad';

        $this->config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(

                    [
                        ['fullTextSearchDisabled', false],
                        ['fullTextSearchMinLength', 4],
                    ]
            );

        $params = FullTextSearchDataComposerParams::create();

        $data = $this->fullTextSearchDataComposer->compose($filter, $params);

        $this->assertEquals(null, $data);
    }

    public function testCompose3()
    {
        $filter = 'test filter';

        $this->config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(

                    [
                        ['fullTextSearchDisabled', true],
                        ['fullTextSearchMinLength', 4],
                    ]
            );

        $params = FullTextSearchDataComposerParams::create();

        $data = $this->fullTextSearchDataComposer->compose($filter, $params);

        $this->assertEquals(null, $data);
    }
}
