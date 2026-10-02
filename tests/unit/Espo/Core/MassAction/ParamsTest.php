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

namespace tests\unit\Espo\Core\MassAction;

use Espo\Core\{
    MassAction\Params,
    Select\SearchParams,
};

use RuntimeException;

class ParamsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testFromRawIds()
    {
        $params = Params::fromRaw(
            [
                'ids' => ['1'],
                'entityType' => 'Test',
            ]
        );

        $this->assertEquals('Test', $params->getEntityType());

        $this->assertEquals(['1'], $params->getIds());

        $this->assertTrue($params->hasIds());
    }

    public function testFromIds()
    {
        $params = Params::createWithIds('Test', ['1']);

        $this->assertEquals('Test', $params->getEntityType());

        $this->assertEquals(['1'], $params->getIds());
    }

    public function testFromSearchParams()
    {
        $searchParams = $this->createMock(SearchParams::class);

        $params = Params::createWithSearchParams('Test', $searchParams);

        $this->assertEquals('Test', $params->getEntityType());

        $this->assertEquals($searchParams, $params->getSearchParams());
    }

    public function testFromRawSearchParams1()
    {
        $where = [
            [
                'type' => 'equals',
                'attribute' => 'name',
                'value' => 'test',
            ]
        ];

        $params = Params::fromRaw(
            [
                'where' => $where,
                'searchParams' => [
                    'primaryFilter' => 'testFilter',
                ],
            ],
            'Test'
        );

        $searchParams = $params->getSearchParams();

        $this->assertEquals('Test', $params->getEntityType());

        $this->assertEquals($where, $searchParams->getWhere()->getRaw()['value']);

        $this->assertEquals('testFilter', $searchParams->getPrimaryFilter());

        $this->assertFalse($params->hasIds());
    }

    public function testFromRawSearchParams2()
    {
        $where = [
            [
                'type' => 'equals',
                'attribute' => 'name',
                'value' => 'test',
            ]
        ];

        $params = Params::fromRaw(
            [
                'searchParams' => [
                    'primaryFilter' => 'testFilter',
                    'where' => $where,
                ],
            ],
            'Test'
        );

        $searchParams = $params->getSearchParams();

        $this->assertEquals($where, $searchParams->getWhere()->getRaw()['value']);

        $this->assertEquals('testFilter', $searchParams->getPrimaryFilter());

        $this->assertFalse($params->hasIds());
    }

    public function testFromRawSearchException1()
    {
        $this->expectException(RuntimeException::class);

        Params::fromRaw(
            [
                'ids' => ['1'],
                'searchParams' => [
                    'primaryFilter' => 'testFilter',
                    'where' => [],
                ],
            ],
            'Test'
        );
    }

    public function testSerialize1(): void
    {
        $params1 = Params::fromRaw(
            [
                'searchParams' => [
                    'primaryFilter' => 'testFilter',
                    'where' => [
                        [
                            'type' => 'equals',
                            'attribute' => 'name',
                            'value' => 'test',
                        ]
                    ],
                ],
            ],
            'Test'
        );

        $params2 = Params::fromSerializedRaw(base64_encode(serialize($params1)));

        $this->assertEquals($params1, $params2);

        $this->assertEquals('equals', $params2->getSearchParams()->getWhere()->getItemList()[0]->getType());
    }
}
