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

namespace tests\unit\Espo\Core\Log;

use Espo\Core\InjectableFactory;
use Espo\Core\Log\DefaultHandlerLoader;
use Espo\Core\Log\EspoRotatingFileHandlerLoader;
use Espo\Core\Log\Handler\EspoRotatingFileHandler;
use Espo\Core\Log\HandlerListLoader;

use PHPUnit\Framework\TestCase;

class HandlerListLoaderTest extends TestCase
{
    private $injectableFactory;

    protected function setUp() : void
    {
        $this->injectableFactory = $this->getMockBuilder(InjectableFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testLoad1()
    {
        $defaultLoader = $this->getMockBuilder(DefaultHandlerLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listLoader = new HandlerListLoader($this->injectableFactory, $defaultLoader);

        $dataList =  [
            [
                'className' => 'Espo\\Core\\Log\\Handler\\EspoRotatingFileHandler',
                'params' => [
                    'filename' => 'data/logs/test-1.log',
                ],
                'level' => 'DEBUG',
                    'formatter' => [
                        'className' => 'Monolog\\Formatter\\LineFormatter',
                        'params' => [
                        'dateFormat' => 'Y-m-d H:i:s',
                    ],
                ]
            ],
            [
            'loaderClassName' => EspoRotatingFileHandlerLoader::class,
                'params' => [
                    'filename' => 'data/logs/test-2.log',
                ],
                'level' => 'NOTICE',
            ],
        ];

        $handler1 = $this->getMockBuilder(EspoRotatingFileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultLoader
            ->expects($this->once())
            ->method('load')
            ->with($dataList[0], 'NOTICE')
            ->willReturn($handler1);

        $loader = $this->getMockBuilder(EspoRotatingFileHandlerLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = $this->getMockBuilder(EspoRotatingFileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->injectableFactory
            ->expects($this->once())
            ->method('create')
            ->with(EspoRotatingFileHandlerLoader::class)
            ->willReturn($loader);

        $params = [
            'filename' => 'data/logs/test-2.log',
            'level' => 'NOTICE',
        ];

        $loader
            ->expects($this->once())
            ->method('load')
            ->with($params)
            ->willReturn($handler);

        $list = $listLoader->load($dataList, 'NOTICE');

        $this->assertCount(2, $list);

        $this->assertInstanceOf(EspoRotatingFileHandler::class, $list[0]);
    }
}
