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

namespace tests\unit\Espo\Core\Utils;

use Espo\Core\Api\Route as RouteItem;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Resource\PathProvider;
use Espo\Core\Utils\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private $route;
    private $filesPath = 'tests/unit/testData/Routes';

    private $pathProvider;
    private $metadata;

    protected function setUp(): void
    {
        $fileManager = new FileManager();

        $this->metadata = $this->createMock(Metadata::class);
        $dataCache = $this->createMock(DataCache::class);
        $this->pathProvider = $this->createMock(PathProvider::class);

        $this->route = new Route(
            $this->metadata,
            $fileManager,
            $dataCache,
            $this->pathProvider,
            $this->createMock(Config\SystemConfig::class),
        );
    }

    private function initPathProvider(string $folder): void
    {
        $this->pathProvider
            ->method('getCustom')
            ->willReturn($this->filesPath . '/' . $folder . '/custom/Espo/Custom/Resources/');

        $this->pathProvider
            ->method('getCore')
            ->willReturn($this->filesPath . '/' . $folder . '/application/Espo/Resources/');

        $this->pathProvider
            ->method('getModule')
            ->willReturnCallback(
                function (?string $moduleName) use ($folder): string {
                    $path = $this->filesPath . '/' . $folder . '/application/Espo/Modules/{*}/Resources/';

                    if ($moduleName === null) {
                        return $path;
                    }

                    return str_replace('{*}', $moduleName, $path);
                }
            );
    }

    public function testUnifyCase1CustomRoutes()
    {
        $this->initPathProvider('testCase1');

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn(
                ['Crm']
            );

        $expected = [
            [
                'adjustedRoute' => '/Custom/{scope}/{id}/{name}',
                'route' => '/Custom/:scope/:id/:name',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Custom',
                        'action' => 'list',
                        'scope' => ':scope',
                        'id' => ':id',
                        'name' => ':name',
                    ],
            ],
            [
                'adjustedRoute' => '/Test',
                'route' => '/Test',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'TestOverridden',
                    ],
            ],
            [
                'adjustedRoute' => '/Activities/{scope}/{id}/{name}',
                'route' => '/Activities/:scope/:id/:name',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Activities',
                        'action' => 'list',
                        'scope' => ':scope',
                        'id' => ':id',
                        'name' => ':name',
                    ],
            ],
            [
                'adjustedRoute' => '/Activities',
                'route' => '/Activities',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Activities',
                        'action' => 'listCalendarEvents',
                    ],
            ],
            [
                'adjustedRoute' => '/App/user',
                'route' => '/App/user',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'App',
                        'action' => 'user',
                    ],
            ],
            [
                'adjustedRoute' => '/Metadata',
                'route' => '/Metadata',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Metadata',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'post',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
        ];

        $expectedItemList = array_map(
            function (array $item) {
                return new RouteItem(
                    $item['method'],
                    $item['route'],
                    $item['adjustedRoute'],
                    $item['params'] ?? [],
                    $item['noAuth'] ?? false,
                    null
                );
            },
            $expected
        );

        $this->assertEquals($expectedItemList, $this->route->getFullList());
    }

    public function testUnifyCase2ModuleRoutes()
    {
        $this->initPathProvider('testCase2');

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn(
                ['Crm', 'Test']
            );

        $expected = [
            [
                'adjustedRoute' => '/Test',
                'route' => '/Test',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Test',
                        'action' => 'listCalendarEvents',
                    ],
            ],
            [
                'adjustedRoute' => '/Activities/{scope}/{id}/{name}',
                'route' => '/Activities/:scope/:id/:name',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Activities',
                        'action' => 'list',
                        'scope' => ':scope',
                        'id' => ':id',
                        'name' => ':name',
                    ],
            ],
            [
                'adjustedRoute' => '/Activities',
                'route' => '/Activities',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Activities',
                        'action' => 'listCalendarEvents',
                    ],
            ],
            [
                'adjustedRoute' => '/App/user',
                'route' => '/App/user',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'App',
                        'action' => 'user',
                    ],
            ],
            [
                'adjustedRoute' => '/Metadata',
                'route' => '/Metadata',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Metadata',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'post',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
        ];

        $expectedItemList = array_map(
            function (array $item) {
                return new RouteItem(
                    $item['method'],
                    $item['route'],
                    $item['adjustedRoute'],
                    $item['params'] ?? [],
                    $item['noAuth'] ?? false,
                    null
                );
            },
            $expected
        );

        $this->assertEquals($expectedItemList, $this->route->getFullList());
    }

    public function testUnifyCase3ModuleRoutesWithRewrites()
    {
        $this->initPathProvider('testCase3');

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn(
                ['Crm', 'Test']
            );

        $expected = [
            [
                'adjustedRoute' => '/Activities/{scope}/{id}/{name}',
                'route' => '/Activities/:scope/:id/:name',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Test',
                        'action' => 'list',
                        'scope' => ':scope',
                        'id' => ':id',
                        'name' => ':name',
                    ],
            ],
            [
                'adjustedRoute' => '/Test',
                'route' => '/Test',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Test',
                        'action' => 'listCalendarEvents',
                    ],
            ],
            [
                'adjustedRoute' => '/Activities',
                'route' => '/Activities',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Activities',
                        'action' => 'listCalendarEvents',
                    ],
            ],
            [
                'adjustedRoute' => '/App/user',
                'route' => '/App/user',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'App',
                        'action' => 'user',
                    ],
            ],
            [
                'adjustedRoute' => '/Metadata',
                'route' => '/Metadata',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => 'Metadata',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'post',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
            [
                'adjustedRoute' => '/{controller}/action/{action}',
                'route' => '/:controller/action/:action',
                'method' => 'get',
                'params' =>
                    [
                        'controller' => ':controller',
                        'action' => ':action',
                    ],
            ],
        ];

        $expectedItemList = array_map(
            function (array $item) {
                return new RouteItem(
                    $item['method'],
                    $item['route'],
                    $item['adjustedRoute'],
                    $item['params'] ?? [],
                    $item['noAuth'] ?? false,
                    false
                );
            },
            $expected
        );

        $this->assertEquals($expectedItemList, $this->route->getFullList());
    }
}
