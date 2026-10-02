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

namespace tests\unit\Espo\Core\Acl\Map;

use Espo\Core\Acl\FieldData;
use Espo\Core\Acl\Map\CacheKeyProvider;
use Espo\Core\Acl\Map\DataBuilder;
use Espo\Core\Acl\Map\Map;
use Espo\Core\Acl\Map\MetadataProvider;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\FieldUtil;

use PHPUnit\Framework\TestCase;
use stdClass;

class MapTest extends TestCase
{
    private $fieldUtil;
    private $systemConfig;
    private $table;
    private $metadataProvider;
    private $cacheKeyProvider;
    private $dataCache;

    protected function setUp(): void
    {
        $this->systemConfig = $this->createMock(Config\SystemConfig::class);
        $this->fieldUtil = $this->createMock(FieldUtil::class);
        $this->table = $this->createMock(Table::class);
        $this->dataCache = $this->createMock(DataCache::class);
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->cacheKeyProvider = $this->createMock(CacheKeyProvider::class);

        $this->systemConfig
            ->expects($this->any())
            ->method('useCache')
            ->willReturn(false);
    }

    private function mockTableData(array $scopeData, array $fieldData, array $permissionData): void
    {
        $returnMap1 = [];

        foreach ($scopeData as $scope => $item) {
            $returnMap1[] = [$scope, ScopeData::fromRaw($item)];
        }

        $this->table
            ->expects($this->any())
            ->method('getScopeData')
            ->willReturnMap($returnMap1);

        $returnMap2 = [];

        foreach ($fieldData as $scope => $item1) {
            foreach ($item1 as $field => $item2) {
                $returnMap2[] = [$scope, $field, FieldData::fromRaw($item2)];
            }
        }

        $this->table
            ->expects($this->any())
            ->method('getFieldData')
            ->willReturnMap($returnMap2);

        $returnMap3 = [];

        foreach ($permissionData as $permission => $level) {
            $returnMap3[] = [$permission, $level];
        }

        $this->table
            ->expects($this->any())
            ->method('getPermissionLevel')
            ->willReturnMap($returnMap3);
    }

    public function testMap1(): void
    {
        $dataBuilder = new DataBuilder($this->metadataProvider, $this->fieldUtil);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getScopeList')
            ->willReturn(['Test1', 'Test2', 'Test3', 'Test4', 'Test5']);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getPermissionList')
            ->willReturn(['assignment', 'portal']);

        $this->metadataProvider
            ->expects($this->any())
            ->method('isScopeEntity')
            ->willReturnMap([
                ['Test1', true],
                ['Test2', true],
                ['Test3', true],
                ['Test4', true],
                ['Test5', false],
            ]);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getScopeFieldList')
            ->willReturnMap([
                ['Test1', ['field1', 'field2', 'field3', 'field4']],
                ['Test2', ['field1']],
                ['Test3', []],
                ['Test4', []],
                ['Test5', []],
            ]);

        $this->fieldUtil
            ->expects($this->any())
            ->method('getAttributeList')
            ->willReturnMap([
                ['Test1', 'field1', ['attr1a', 'attr1b']],
                ['Test1', 'field2', ['field2']],
                ['Test1', 'field3', ['field3']],
                ['Test1', 'field4', ['field4']],
                ['Test2', 'field1', ['field1']],
            ]);

        $this->mockTableData(
            [
                'Test1' => (object) [
                    Table::ACTION_CREATE => Table::LEVEL_YES,
                    Table::ACTION_READ => Table::LEVEL_TEAM,
                ],
                'Test2' => (object) [
                    Table::ACTION_CREATE => Table::LEVEL_YES,
                    Table::ACTION_READ => Table::LEVEL_TEAM,
                    Table::ACTION_EDIT => Table::LEVEL_OWN,
                ],
                'Test3' => false,
                'Test4' => true,
                'Test5' => true,
            ],
            [
                'Test1' => [
                    'field1' => (object) [
                        Table::ACTION_READ => Table::LEVEL_YES,
                        Table::ACTION_EDIT => Table::LEVEL_NO,
                    ],
                    'field2' => (object) [
                        Table::ACTION_READ => Table::LEVEL_NO,
                        Table::ACTION_EDIT => Table::LEVEL_YES,
                    ],
                    'field3' => (object) [
                        Table::ACTION_READ => Table::LEVEL_NO,
                        Table::ACTION_EDIT => Table::LEVEL_NO,
                    ],
                    'field4' => (object) [
                        Table::ACTION_READ => Table::LEVEL_YES,
                        Table::ACTION_EDIT => Table::LEVEL_YES,
                    ],
                ],
                'Test2' => [
                    'field1' => (object) [
                        Table::ACTION_READ => Table::LEVEL_NO,
                        Table::ACTION_EDIT => Table::LEVEL_NO,
                    ],
                ],
            ],
            [
                'assignment' => Table::LEVEL_YES,
                'portal' => Table::LEVEL_NO,
            ],
        );

        $expectedData = $this->getExpectedRawData();

        $map = new Map(
            $this->table,
            $dataBuilder,
            $this->dataCache,
            $this->cacheKeyProvider,
            $this->systemConfig,
        );

        $this->assertEquals($expectedData, $map->getData());

        $this->assertEquals(
            ['field2', 'field3'],
            $map->getScopeForbiddenFieldList('Test1', Table::ACTION_READ)
        );

        $this->assertEquals(
            ['field1', 'field3'],
            $map->getScopeForbiddenFieldList('Test1', Table::ACTION_EDIT)
        );

        $this->assertEquals(
            ['field2', 'field3'],
            $map->getScopeForbiddenAttributeList('Test1', Table::ACTION_READ)
        );

        $this->assertEquals(
            ['attr1a', 'attr1b', 'field3'],
            $map->getScopeForbiddenAttributeList('Test1', Table::ACTION_EDIT)
        );

        $this->assertEquals(
            ['attr1a', 'attr1b', 'field3'],
            $map->getScopeForbiddenAttributeList('Test1', Table::ACTION_EDIT)
        );

        $this->assertEquals(
            ['field1'],
            $map->getScopeForbiddenFieldList('Test2', Table::ACTION_READ)
        );

        $this->assertEquals(
            ['field1'],
            $map->getScopeForbiddenFieldList('Test2', Table::ACTION_READ)
        );

        $this->assertEquals(
            [],
            $map->getScopeForbiddenFieldList('Test3', Table::ACTION_READ)
        );
    }

    private function getExpectedRawData(): stdClass
    {
        return (object) [
          'table' => (object) [
            'Test1' => (object) [
              'read' => 'team',
              'stream' => 'no',
              'edit' => 'no',
              'delete' => 'no',
              'create' => 'yes'
            ],
            'Test2' => (object) [
              'read' => 'team',
              'stream' => 'no',
              'edit' => 'own',
              'delete' => 'no',
              'create' => 'yes'
            ],
            'Test3' => false,
            'Test4' => true,
            'Test5' => true
          ],
          'fieldTable' => (object) [
            'Test1' => (object) [
              'field1' => (object) [
                'read' => 'yes',
                'edit' => 'no'
              ],
              'field2' => (object) [
                'read' => 'no',
                'edit' => 'yes'
              ],
              'field3' => (object) [
                'read' => 'no',
                'edit' => 'no'
              ]
            ],
            'Test2' => (object) [
              'field1' => (object) [
                'read' => 'no',
                'edit' => 'no'
              ]
            ],
            'Test3' => (object) [],
            'Test4' => (object) []
          ],
          'assignmentPermission' => 'yes',
          'portalPermission' => 'no',
          'fieldTableQuickAccess' => (object) [
            'Test1' => (object) [
              'attributes' => (object) [
                'read' => (object) [
                  'yes' => [
                    'attr1a',
                    'attr1b'
                  ],
                  'no' => [
                    'field2',
                    'field3'
                  ]
                ],
                'edit' => (object) [
                  'yes' => [
                    'field2'
                  ],
                  'no' => [
                    'attr1a',
                    'attr1b',
                    'field3'
                  ]
                ]
              ],
              'fields' => (object) [
                'read' => (object) [
                  'yes' => [
                    'field1'
                  ],
                  'no' => [
                    'field2',
                    'field3'
                  ]
                ],
                'edit' => (object) [
                  'yes' => [
                   'field2'
                  ],
                  'no' => [
                    'field1',
                    'field3'
                  ]
                ]
              ]
            ],
            'Test2' => (object) [
              'attributes' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => [
                    'field1'
                  ]
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => [
                    'field1'
                  ]
                ]
              ],
              'fields' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => [
                    'field1'
                  ]
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => [
                    'field1'
                  ]
                ]
              ]
            ],
            'Test3' => (object) [
              'attributes' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => []
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => []
                ]
              ],
              'fields' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => []
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => []
                ]
              ]
            ],
            'Test4' => (object) [
              'attributes' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => []
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => []
                ]
              ],
              'fields' => (object) [
                'read' => (object) [
                  'yes' => [],
                  'no' => []
                ],
                'edit' => (object) [
                  'yes' => [],
                  'no' => []
                ]
              ]
            ]
          ]
        ];
    }
}
