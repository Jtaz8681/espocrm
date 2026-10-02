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

use Espo\Core\Utils\Module;
use Espo\Core\Utils\File\Manager as FileManager;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    /** @var Module */
    private $module;

    /** @var FileManager */
    private $fileManager;

    protected function setUp(): void
    {
        $this->fileManager = $this->createMock(FileManager::class);
        $this->module = new Module($this->fileManager);
    }

    public function testOrder1(): void
    {
        $this->fileManager
            ->expects(self::any())
            ->method('getDirList')
            ->willReturnMap([
                [
                    'application/Espo/Modules',
                    ['M01', 'M02', 'M1', 'M2'],
                ],
                [
                    'custom/Espo/Modules',
                    ['M3', 'M4', 'M51', 'M52'],
                ]
            ]);

        $this->fileManager
            ->expects(self::any())
            ->method('exists')
            ->willReturnMap([
                ['application/Espo/Modules/M01/Resources/module.json', true],
                ['application/Espo/Modules/M02/Resources/module.json', true],
                ['application/Espo/Modules/M1/Resources/module.json', true],
                ['application/Espo/Modules/M2/Resources/module.json', true],
                ['custom/Espo/Modules/M3/Resources/module.json', true],
                ['custom/Espo/Modules/M4/Resources/module.json', true],
                ['custom/Espo/Modules/M51/Resources/module.json', true],
                ['custom/Espo/Modules/M52/Resources/module.json', true],
            ]);

        $this->fileManager
            ->expects(self::any())
            ->method('getContents')
            ->willReturnMap([
                ['application/Espo/Modules/M01/Resources/module.json', '{"order": 11}'],
                ['application/Espo/Modules/M02/Resources/module.json', '{"order": 11}'],
                ['application/Espo/Modules/M1/Resources/module.json', '{"order": 4}'],
                ['application/Espo/Modules/M2/Resources/module.json', '{"order": 3}'],
                ['custom/Espo/Modules/M3/Resources/module.json', '{"order": 2}'],
                ['custom/Espo/Modules/M4/Resources/module.json', '{"order": 1}'],
                ['custom/Espo/Modules/M51/Resources/module.json', '{"order": 12}'],
                ['custom/Espo/Modules/M52/Resources/module.json', '{"order": 12}'],
            ]);

        $this->assertEquals(
            ['M4', 'M3', 'M2', 'M1', 'M01', 'M02', 'M51', 'M52'],
            $this->module->getOrderedList()
        );
    }
}
