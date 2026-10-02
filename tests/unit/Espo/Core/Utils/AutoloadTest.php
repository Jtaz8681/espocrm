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

use Espo\Core\Utils\Autoload;
use Espo\Core\Utils\Autoload\Loader;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Resource\PathProvider;
use PHPUnit\Framework\TestCase;

class AutoloadTest extends TestCase
{
    private ?Config\SystemConfig $systemConfig = null;

    private $metadata;
    private $fileManager;
    private $loader;
    private $pathProvider;
    private $autoload;

    protected function setUp(): void
    {
        $this->systemConfig = $this->createMock(Config\SystemConfig::class);
        $this->metadata = $this->createMock(Metadata::class);
        $dataCache = $this->createMock(DataCache::class);
        $this->fileManager = $this->createMock(FileManager::class);
        $this->loader = $this->createMock(Loader::class);
        $this->pathProvider = $this->createMock(PathProvider::class);

        $this->initPathProvider();

        $this->autoload = new Autoload(
            $this->metadata,
            $dataCache,
            $this->fileManager,
            $this->loader,
            $this->pathProvider,
            $this->systemConfig,
        );
    }

    private function initPathProvider(string $rootPath = ''): void
    {
        $this->pathProvider
            ->method('getCustom')
            ->willReturn($rootPath . 'custom/Espo/Custom/Resources/');

        $this->pathProvider
            ->method('getCore')
            ->willReturn($rootPath . 'application/Espo/Resources/');

        $this->pathProvider
            ->method('getModule')
            ->willReturnCallback(
                function (?string $moduleName) use ($rootPath): string {
                    $path = $rootPath . 'application/Espo/Modules/{*}/Resources/';

                    if ($moduleName === null) {
                        return $path;
                    }

                    return str_replace('{*}', $moduleName, $path);
                }
            );
    }

    public function testMerge()
    {
        $this->metadata
            ->expects($this->once())
            ->method('getModuleList')
            ->willReturn(['M1', 'M2']);

        $this->systemConfig
            ->expects($this->once())
            ->method('useCache')
            ->willReturn(false);

        $this->fileManager
            ->expects($this->any())
            ->method('isFile')
            ->willReturnMap(

                    [
                        ['application/Espo/Resources/autoload.json', false],
                        ['application/Espo/Modules/M1/Resources/autoload.json', true],
                        ['application/Espo/Modules/M2/Resources/autoload.json', true],
                        ['custom/Espo/Custom/Resources/autoload.json', false],
                    ]

            );

        $data1 = [
            'autoloadFileList' => ['f1.php'],
            'psr-4' => [
                't1' => 'r1',
            ],
        ];

        $data2 = [
            'autoloadFileList' => ['f2.php'],
            'psr-4' => [
                't2' => 'r2',
            ],
        ];

        $expectedData = [
            'autoloadFileList' => ['f1.php', 'f2.php'],
            'psr-4' => [
                't1' => 'r1',
                't2' => 'r2',
            ],
        ];

        $this->fileManager
            ->expects($this->any())
            ->method('getContents')
            ->willReturnMap(
                [
                    ['application/Espo/Modules/M1/Resources/autoload.json', json_encode($data1)],
                    ['application/Espo/Modules/M2/Resources/autoload.json', json_encode($data2)],
                ]
            );

        $this->loader
            ->expects($this->once())
            ->method('register')
            ->with($expectedData);

        $this->autoload->register();
    }
}
