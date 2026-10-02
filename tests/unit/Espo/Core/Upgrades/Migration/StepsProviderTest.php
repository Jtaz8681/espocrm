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

namespace tests\unit\Espo\Core\Upgrades\Migration;

use Espo\Core\Upgrades\Migration\StepsProvider;
use Espo\Core\Utils\File\Manager;
use PHPUnit\Framework\TestCase;

class StepsProviderTest extends TestCase
{
    public function testGet1(): void
    {
        $fileManager = $this->createMock(Manager::class);

        $fileManager
            ->expects($this->once())
            ->method('getDirList')
            ->willReturn(['V7_5_1', 'V8_0', 'V8_1', 'V8_2', 'V8_2_2']);

        $fileManager
            ->expects($this->any())
            ->method('isFile')
            ->willReturn(true);

        $provider = new StepsProvider($fileManager);

        $this->assertEquals([
            '7.5.1',
            '8.0',
            '8.1',
            '8.2',
            '8.2.2',
        ], $provider->getAfterUpgrade());
    }
}
