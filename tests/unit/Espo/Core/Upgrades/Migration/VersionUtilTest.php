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

use Espo\Core\Upgrades\Migration\VersionUtil;
use PHPUnit\Framework\TestCase;

class VersionUtilTest extends TestCase
{
    public function testStepToVersion(): void
    {
        $this->assertEquals('8.0.0', VersionUtil::stepToVersion('8.0'));
        $this->assertEquals('8.0.1', VersionUtil::stepToVersion('8.0.1'));
        $this->assertEquals('8.1.0', VersionUtil::stepToVersion('8.1.0'));
    }

    public function testGet1(): void
    {
        $list = VersionUtil::extractSteps('8.0.0', '8.3.0', [
            '7.0',
            '7.5',
            '8.0',
            '8.0.1',
            '8.0.3',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.1',
        ]);

        $this->assertEquals([
            '8.0.1',
            '8.0.3',
            '8.1',
            '8.3',
        ], $list);
    }

    public function testGet2(): void
    {
        $list = VersionUtil::extractSteps('8.0.0', '8.3.1', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.1',
        ]);

        $this->assertEquals([
            '8.1',
            '8.3',
        ], $list);
    }

    public function testGet3(): void
    {
        $list = VersionUtil::extractSteps('8.0.0', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
        ]);

        $this->assertEquals([
            '8.1',
            '8.3',
        ], $list);
    }

    public function testGet4(): void
    {
        $list = VersionUtil::extractSteps('8.2.0', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
        ]);

        $this->assertEquals([
            '8.3',
        ], $list);
    }

    public function testGet5(): void
    {
        $list = VersionUtil::extractSteps('8.0.4', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
        ]);

        $this->assertEquals([
            '8.1',
            '8.3',
        ], $list);
    }

    public function testGetMajor1(): void
    {
        $list = VersionUtil::extractSteps('7.5.4', '8.3.5', array_reverse([
            '7.0',
            '7.5',
            '7.5.1',
            '7.6',
            '7.6.1',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
        ]));

        $this->assertEquals([
            '7.6',
            '8.0',
            '8.1',
            '8.3',
        ], $list);
    }

    public function testGetPatch1(): void
    {
        $list = VersionUtil::extractSteps('8.3.0', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
            '8.3.4',
        ]);

        $this->assertEquals([
            '8.3.2',
            '8.3.4',
        ], $list);
    }

    public function testGetPatch2(): void
    {
        $list = VersionUtil::extractSteps('8.3.0', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
            '8.3.4',
            '8.3.5',
            '8.6.1',
        ]);

        $this->assertEquals([
            '8.3.2',
            '8.3.4',
            '8.3.5',
        ], $list);
    }

    public function testGetPatch3(): void
    {
        $list = VersionUtil::extractSteps('8.3.0', '8.3.5', [
            '8.3',
            '8.3.0-beta',
            '8.3.0-beta.1',
            '8.3.0-beta.2',
            '8.3.2',
            '8.3.4',
            '8.3.5',
            '8.6.1',
        ]);

        $this->assertEquals([
            '8.3.2',
            '8.3.4',
            '8.3.5',
        ], $list);
    }

    public function testGetPatch4(): void
    {
        $list = VersionUtil::extractSteps('8.3.0-beta.1', '8.3.0', [
            '8.3',
            '8.3.0-beta.1',
            '8.3.0-beta.2',
            '8.3.0',
        ]);

        $this->assertEquals([
            '8.3.0-beta.2',
            '8.3.0',
        ], $list);
    }

    public function testGetSame1(): void
    {
        $list = VersionUtil::extractSteps('8.3.5', '8.3.5', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
            '8.3.4',
            '8.3.5',
            '8.6.1',
        ]);

        $this->assertEquals([], $list);
    }

    public function testGetSame2(): void
    {
        $list = VersionUtil::extractSteps('8.6.1', '8.6.1', [
            '7.0',
            '7.5',
            '8.0',
            '8.1',
            '8.1.4',
            '8.3',
            '8.3.2',
            '8.3.4',
            '8.3.5',
            '8.6.1',
        ]);

        $this->assertEquals([], $list);
    }
}
