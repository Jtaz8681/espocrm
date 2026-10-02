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

namespace tests\unit\Espo\Core\Utils\Config;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Config\ConfigWriterFileManager;
use Espo\Core\Utils\Config\ConfigWriterHelper;
use Espo\Core\Utils\Config\InternalConfigHelper;
use PHPUnit\Framework\TestCase;

class ConfigWriterTest extends TestCase
{
    private $fileManager;
    private $config;
    private $helper;
    private $internalConfigHelper;
    private $configWriter;

    private $configPath;
    private $internalConfigPath;
    private $stateConfigPath;

    protected function setUp(): void
    {
        $this->fileManager = $this->createMock(ConfigWriterFileManager::class);
        $this->config = $this->createMock(Config::class);
        $this->helper = $this->createMock(ConfigWriterHelper::class);
        $this->internalConfigHelper = $this->createMock(InternalConfigHelper::class);

        $this->configWriter = new ConfigWriter(
            $this->config,
            $this->fileManager,
            $this->helper,
            $this->internalConfigHelper
        );

        $this->configPath = 'somePath';
        $this->internalConfigPath = 'internalSomePath';
        $this->stateConfigPath = 'stateSomePath';

        $this->config
            ->expects($this->any())
            ->method('getConfigPath')
            ->willReturn($this->configPath);

        $this->config
            ->expects($this->any())
            ->method('getInternalConfigPath')
            ->willReturn($this->internalConfigPath);

        $this->config
            ->expects($this->any())
            ->method('getStateConfigPath')
            ->willReturn($this->stateConfigPath);
    }

    public function testSave1(): void
    {
        $this->configWriter->set('k1', 'v1');

        $this->configWriter->setMultiple([
            'k2' => 'v2',
            'k3' => 'v3',
        ]);

        $this->configWriter->remove('k4');

        $previousData = [
            'k3' => 'e3',
            'k4' => 'e4',
            'microtime' => 0.0,
            'cacheTimestamp' => 0,
        ];

        $newData = [
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            'microtime' => 1.0,
            'cacheTimestamp' => 1,
        ];

        $this->helper
            ->expects($this->once())
            ->method('generateMicrotime')
            ->willReturn(1.0);

        $this->helper
            ->expects($this->once())
            ->method('generateCacheTimestamp')
            ->willReturn(1);

        $this->config
            ->expects($this->once())
            ->method('update');

        $this->fileManager
            ->expects($this->any())
            ->method('isFile')
            ->willReturnMap([
                [$this->configPath, true],
                [$this->internalConfigPath, false],
                [$this->stateConfigPath, false],
            ]);

        $this->fileManager
            ->expects($this->once())
            ->method('putPhpContents')
            ->with($this->configPath, $newData);

        $this->fileManager
            ->expects($this->exactly(2))
            ->method('getPhpContents')
            ->willReturnMap([
                [$this->configPath, $previousData],
                [$this->configPath, $previousData],
            ]);

        $this->configWriter->save();
    }

    public function testSave2(): void
    {
        $this->configWriter->set('k1', 'v1');
        $this->configWriter->set('k2', 'v2');

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForInternalConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['k2', true],
                    ['cacheTimestamp', false],
                ]
            );

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForStateConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['k2', false],
                    ['cacheTimestamp', true],
                ]
            );

        $this->helper
            ->expects($this->exactly(3))
            ->method('generateMicrotime')
            ->willReturn(1.0);

        $this->helper
            ->expects($this->once())
            ->method('generateCacheTimestamp')
            ->willReturn(1);

        $this->fileManager
            ->expects(self::any())
            ->method('isFile')
            ->willReturnMap([
                [$this->configPath, true],
                [$this->internalConfigPath, true],
                [$this->stateConfigPath, true],
            ]);

        $this->fileManager
            ->expects($this->exactly(6))
            ->method('getPhpContents')
            ->willReturnMap([
                [$this->configPath, []],
                [$this->internalConfigPath, []],
                [$this->stateConfigPath, []],
            ]);

        $this->fileManager
            ->expects(self::any())
            ->method('putPhpContents')
            ->willReturnMap([
                [
                    $this->internalConfigPath,
                    ['k2' => 'v2', 'microtimeInternal' => 1.0]
                ],
                [
                    $this->configPath,
                    ['k1' => 'v1', 'microtime' => 1.0]
                ],
                [
                    $this->stateConfigPath,
                    ['cacheTimestamp' => 1, 'microtimeState' => 1.0]
                ],
            ]);

        $this->configWriter->save();
    }

    public function testSave3(): void
    {
        $this->configWriter->set('k1', 'v1');
        $this->configWriter->set('k2', 'v2');

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForInternalConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['k2', true],
                    ['cacheTimestamp', false],
                ]
            );

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForStateConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['k2', false],
                    ['cacheTimestamp', true],
                ]
            );

        $this->helper
            ->expects($this->exactly(3))
            ->method('generateMicrotime')
            ->willReturn(1.0);

        $this->helper
            ->expects($this->once())
            ->method('generateCacheTimestamp')
            ->willReturn(1);

        $this->fileManager
            ->expects(self::any())
            ->method('isFile')
            ->willReturnMap([
                [$this->configPath, true],
                [$this->internalConfigPath, true],
                [$this->stateConfigPath, true],
            ]);

        $this->fileManager
            ->expects($this->exactly(6))
            ->method('getPhpContents')
            ->willReturnMap([
                [$this->configPath, []],
                [$this->internalConfigPath, []],
                [$this->stateConfigPath, []],
            ]);

        $this->fileManager
            ->expects(self::any())
            ->method('putPhpContents')
            ->willReturnMap([
                [
                    $this->internalConfigPath,
                    ['k2' => 'v2', 'microtimeInternal' => 1.0]
                ],
                [
                    $this->configPath,
                    ['k1' => 'v1', 'microtime' => 1.0]
                ],
                [
                    $this->stateConfigPath,
                    ['cacheTimestamp' => 1, 'microtimeState' => 1.0]
                ],
            ]);

        $this->configWriter->save();
    }

    public function testsRemove1(): void
    {
        $this->configWriter->remove('k1');

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForInternalConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['cacheTimestamp', false],
                ]
            );

        $this->internalConfigHelper
            ->expects($this->any())
            ->method('isParamForStateConfig')
            ->willReturnMap(
                [
                    ['k1', false],
                    ['cacheTimestamp', false],
                ]
            );

        $this->helper
            ->expects($this->once())
            ->method('generateCacheTimestamp')
            ->willReturn(1);

        $this->fileManager
            ->expects(self::any())
            ->method('isFile')
            ->willReturnMap([
                [$this->configPath, true],
                [$this->internalConfigPath, true],
                [$this->stateConfigPath, true],
            ]);

        $this->fileManager
            ->expects(self::any())
            ->method('isFile')
            ->willReturnMap([
                [$this->configPath, true],
                [$this->internalConfigPath, true],
                [$this->stateConfigPath, true],
            ]);

        $this->fileManager
            ->expects($this->exactly(4))
            ->method('getPhpContents')
            ->willReturnMap([
                [$this->configPath, ['k1' => 'v1', 'k2' => 'v2']],
                [$this->internalConfigPath, []],
                [$this->stateConfigPath, []],
            ]);

        $this->fileManager
            ->expects($this->exactly(1))
            ->method('putPhpContents')
            ->willReturnMap([
                [
                    $this->configPath,
                    ['k2' => 'v2', 'microtime' => 1.0]
                ],
            ]);

        $this->configWriter->save();
    }
}
