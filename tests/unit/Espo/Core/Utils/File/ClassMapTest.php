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

namespace tests\unit\Espo\Core\Utils\File;

use PHPUnit\Framework\TestCase;
use tests\unit\ReflectionHelper;
use Espo\Core\Utils\File\ClassMap;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Module;
use Espo\Core\Utils\Module\PathProvider;

class ClassMapTest extends TestCase
{
    /** @var ClassMap */
    private $classMap;
    private $dataCache;

    protected $reflection;

    private $systemConfig;

    private $customPath = 'tests/unit/testData/EntryPoints/Espo/Custom/';
    private $corePath = 'tests/unit/testData/EntryPoints/Espo/';
    private $modulePath = 'tests/unit/testData/EntryPoints/Espo/Modules/{*}/';

    /** @var Module */
    private $module;

    protected function setUp(): void
    {
        $fileManager = new FileManager();

        $this->systemConfig = $this->createMock(Config\SystemConfig::class);
        $this->module = $this->createMock(Module::class);
        $this->dataCache = $this->createMock(DataCache::class);

        $pathProvider = $this->createMock(PathProvider::class);

        $pathProvider
            ->method('getCustom')
            ->willReturn($this->customPath);

        $pathProvider
            ->method('getCore')
            ->willReturn($this->corePath);

        $pathProvider
            ->method('getModule')
            ->willReturnCallback(
                function (?string $moduleName): string {
                    if ($moduleName === null) {
                        return $this->modulePath;
                    }

                    return str_replace('{*}', $moduleName, $this->modulePath);
                }
            );

        $this->module
            ->method('getOrderedList')
            ->willReturn(['Crm']);

        $this->classMap = new ClassMap(
            $fileManager,
            $this->module,
            $this->dataCache,
            $pathProvider,
            $this->systemConfig,
        );

        $this->reflection = new ReflectionHelper($this->classMap);
    }

    public function testGetDataWithNoCache1(): void
    {
        $expected = [
            'Download' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\Download',
            'TestEntry' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\TestEntry',
            'InModule' => 'tests\unit\testData\EntryPoints\Espo\Modules\Crm\EntryPoints\InModule'
        ];

        $this->assertEquals($expected, $this->classMap->getData('EntryPoints', null, ['run']));
    }

    public function testGetDataWithNoCache2(): void
    {
        $this->dataCache
            ->expects($this->once())
            ->method('has')
            ->with('entryPoints')
            ->willReturn(true);

        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(false);

        $this->module
            ->expects($this->once())
            ->method('getOrderedList')
            ->willReturn(
                ['Crm']
            );

        $cacheKey = 'entryPoints';

        $result = [
            'Download' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\Download',
            'TestEntry' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\TestEntry',
            'InModule' => 'tests\unit\testData\EntryPoints\Espo\Modules\Crm\EntryPoints\InModule',
       ];

        $this->assertEquals(
            $result,
            $this->classMap->getData('EntryPoints', $cacheKey, ['run'])
        );
    }

    public function testGetDataWithCache(): void
    {
        $result = [
            'Download' => 'tests\\unit\\testData\\EntryPoints\\Espo\\EntryPoints\\Download',
        ];

        $this->systemConfig
            ->expects($this->once())
            ->method('useCache')
            ->willReturn(true);

        $cacheKey = 'entryPoints';

        $this->dataCache
            ->expects($this->once())
            ->method('has')
            ->with('entryPoints')
            ->willReturn(true);

        $this->dataCache
            ->expects($this->once())
            ->method('get')
            ->with('entryPoints')
            ->willReturn($result);

        $this->module
            ->expects($this->never())
            ->method('getOrderedList');

        $this->assertEquals($result, $this->classMap->getData('EntryPoints', $cacheKey, ['run']));
    }

    public function testGetDataWithNoCacheString(): void
    {
        $this->systemConfig
            ->expects($this->exactly(2))
            ->method('useCache')
            ->willReturn(true);

        $this->module
            ->expects($this->once())
            ->method('getOrderedList')
            ->willReturn(
                ['Crm']
            );

        $cacheKey = 'entryPoints';

        $result = [
            'Download' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\Download',
            'TestEntry' => 'tests\unit\testData\EntryPoints\Espo\EntryPoints\TestEntry',
            'InModule' => 'tests\unit\testData\EntryPoints\Espo\Modules\Crm\EntryPoints\InModule',
        ];

        $this->dataCache
            ->expects($this->once())
            ->method('has')
            ->with($cacheKey)
            ->willReturn(true);

        $this->dataCache
            ->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(null);

        $this->assertEquals(
            $result,
            $this->classMap->getData('EntryPoints', $cacheKey, ['run'])
        );
    }
}
