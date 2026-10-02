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

namespace tests\unit\Espo\Core;

use PHPUnit\Framework\TestCase;
use tests\unit\ReflectionHelper;

use Espo\Core\Hook\GeneralInvoker;
use Espo\Core\HookManager;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Module\PathProvider;

class HookManagerTest extends TestCase
{
    private $filesPath = 'tests/unit/testData/Hooks';

    private ?Config\SystemConfig $systemConfig = null;

    private $pathProvider;
    private $reflection;
    private $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->createMock(Metadata::class);
            $this->getMockBuilder(Metadata::class)->disableOriginalConstructor()->getMock();

        $this->systemConfig = $this->createMock(Config\SystemConfig::class);

        $injectableFactory = $this->createMock(InjectableFactory::class);
        $dataCache = $this->createMock(DataCache::class);
        $fileManager = new FileManager();
        $this->pathProvider = $this->createMock(PathProvider::class);
        $generalInvoker = $this->createMock(GeneralInvoker::class);

        $hookManager = new HookManager(
            $injectableFactory,
            $fileManager,
            $this->metadata,
            $dataCache,
            $this->createMock(Log::class),
            $this->pathProvider,
            $generalInvoker,
            $this->systemConfig,
        );

        $this->reflection = new ReflectionHelper($hookManager);
    }

    private function initPathProvider(string $folder): void
    {
        $this->pathProvider
            ->method('getCustom')
            ->willReturn($this->filesPath . '/' . $folder . '/custom/Espo/Custom/');

        $this->pathProvider
            ->method('getCore')
            ->willReturn($this->filesPath . '/' . $folder . '/application/Espo/');

        $this->pathProvider
            ->method('getModule')
            ->willReturnCallback(
                function (?string $moduleName) use ($folder): string {
                    $path = $this->filesPath . '/' . $folder . '/application/Espo/Modules/{*}/';

                    if ($moduleName === null) {
                        return $path;
                    }

                    return str_replace('{*}', $moduleName, $path);
                }
            );
    }

    public function testHookExists(): void
    {
        $data = array (
            'Espo\\Hooks\\Note\\Stream' => 8,
            'Espo\\Hooks\\Note\\Mentions' => 9,
            'Espo\\Hooks\\Note\\Notifications' => 14,
        );

        $data = array (
          array (
            'className' => 'Espo\\Hooks\\Note\\Stream',
            'order' => 8,
          ),
          array (
            'className' => 'Espo\\Hooks\\Note\\Mentions',
            'order' => 9,
          ),
          array (
            'className' => 'Espo\\Hooks\\Note\\Notifications',
            'order' => 14,
          ),
        );

        $this->assertTrue(
            $this->reflection->invokeMethod('hookExists', array('Espo\\Hooks\\Note\\Mentions', $data))
        );
        $this->assertTrue(
            $this->reflection->invokeMethod('hookExists', array('Espo\\Modules\\Crm\\Hooks\\Note\\Mentions', $data))
        );
        $this->assertTrue(
            $this->reflection->invokeMethod('hookExists', array('Espo\\Modules\\Test\\Hooks\\Note\\Mentions', $data))
        );
        $this->assertTrue(
            $this->reflection->invokeMethod('hookExists', array('Espo\\Modules\\Test\\Hooks\\Common\\Stream', $data))
        );
        $this->assertFalse(
            $this->reflection->invokeMethod('hookExists', array('Espo\\Hooks\\Note\\TestHook', $data))
        );
    }

    public function testSortHooks()
    {
        $data = array (
            'Common' =>
            array (
              'afterSave' =>
              array (
                array (
                    'className' => 'Espo\\Hooks\\Common\\AssignmentEmailNotification',
                    'order' => 9,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\Notifications',
                    'order' => 10,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\Stream',
                    'order' => 9,
                ),
              ),
              'beforeSave' =>
              array (
                array (
                    'className' => 'Espo\\Hooks\\Common\\Formula',
                    'order' => 5,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\NextNumber',
                    'order' => 10,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\CurrencyConverted',
                    'order' => 1,
                ),
              ),
            ),
            'Note' =>
            array (
              'beforeSave' =>
              array (
                array (
                    'className' => 'Espo\\Hooks\\Note\\Mentions',
                    'order' => 9,
                ),
              ),
              'afterSave' =>
              array (
                array (
                    'className' => 'Espo\\Hooks\\Note\\Notifications',
                    'order' => 14,
                ),
              ),
            ),
        );

        $result = array (
          'Common' =>
          array (
            'afterSave' =>
            array (
                array (
                    'className' => 'Espo\\Hooks\\Common\\AssignmentEmailNotification',
                    'order' => 9,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\Stream',
                    'order' => 9,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\Notifications',
                    'order' => 10,
                ),
            ),
            'beforeSave' =>
            array (
                array (
                    'className' => 'Espo\\Hooks\\Common\\CurrencyConverted',
                    'order' => 1,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\Formula',
                    'order' => 5,
                ),
                array (
                    'className' => 'Espo\\Hooks\\Common\\NextNumber',
                    'order' => 10,
                ),
            ),
          ),
          'Note' =>
          array (
            'beforeSave' =>
            array (
                array (
                    'className' => 'Espo\\Hooks\\Note\\Mentions',
                    'order' => 9,
                ),
            ),
            'afterSave' =>
            array (
                array (
                    'className' => 'Espo\\Hooks\\Note\\Notifications',
                    'order' => 14,
                ),
            ),
          ),
        );

        $this->assertEquals($result, $this->reflection->invokeMethod('sortHooks', array($data)) );
    }

    public function testCase1CustomHook()
    {
        $this->initPathProvider('testCase1');

        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(false);

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn([
                'Crm',
                'Test',
            ]);

        $this->reflection->invokeMethod('loadHooks');

        $result = [
          'Note' =>
          [
            'beforeSave' =>
            [
                [
                    'className' => 'tests\\unit\\testData\\Hooks\\testCase1\\custom\\Espo\\Custom\\Hooks\\Note\\Mentions',
                    'order' => 7,
                ],
            ],
          ],
        ];

        $this->assertEquals($result, $this->reflection->getProperty('data'));
    }

    public function testCase2ModuleHook1()
    {
        $this->initPathProvider('testCase2');

        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(false);

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn([
                'Crm',
                'Test',
            ]);

        $this->reflection->invokeMethod('loadHooks');

        $result = [
          'Note' =>
          [
            'beforeSave' =>
            [
                [
                    'className' =>
                    'tests\\unit\\testData\\Hooks\\testCase2\\application\\Espo\\Modules\\Crm\\Hooks\\Note\\Mentions',
                    'order' => 9,
                ],
            ],
          ],
        ];

        $this->assertEquals($result, $this->reflection->getProperty('data'));
    }

    public function testCase2ModuleHookReverseModuleOrder()
    {
        $this->initPathProvider('testCase2');

        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(false);

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn([
                'Test',
                'Crm',
            ]);

        $this->reflection->invokeMethod('loadHooks');

        $result = [
          'Note' =>
          [
            'beforeSave' =>
            [
                [
                    'className' =>
                        'tests\\unit\\testData\\Hooks\\testCase2\\application\\Espo\\Modules\\Test\\Hooks\\Note\\Mentions',
                    'order' => 9,
                ],
            ],
          ],
        ];

        $this->assertEquals($result, $this->reflection->getProperty('data'));
    }

    public function testCase3CoreHook()
    {
        $this->initPathProvider('testCase3');

        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(false);

        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn([]);

        $this->reflection->invokeMethod('loadHooks');

        $result = array (
          'Note' =>
          array (
            'beforeSave' =>
            array (
                array (
                    'className' => 'tests\\unit\\testData\\Hooks\\testCase3\\application\\Espo\\Hooks\\Note\\Mentions',
                    'order' => 9,
                ),
            ),
          ),
        );

        $this->assertEquals($result, $this->reflection->getProperty('data'));
    }
}
