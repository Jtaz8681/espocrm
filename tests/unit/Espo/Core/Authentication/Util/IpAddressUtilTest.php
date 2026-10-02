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

namespace tests\unit\Espo\Core\Authentication\Util;

use PHPUnit\Framework\TestCase;

class IpAddressUtilTest extends TestCase
{
    public function testWhitelist(): void
    {
        $util = new \Espo\Core\Authentication\Util\IpAddressUtil();

        $this->assertTrue(
            $util->isInWhitelist('192.168.0.1', [
                '192.168.0.1',
            ])
        );

        $this->assertFalse(
            $util->isInWhitelist('192.168.0.1', [
                '192.168.0.0',
            ])
        );

        $this->assertTrue(
            $util->isInWhitelist('192.168.0.1', [
                '192.168.0.1',
                '192.168.0.1',
            ])
        );

        $this->assertTrue(
            $util->isInWhitelist('192.168.0.5', [
                '192.168.0.1',
                '192.168.0.1/24',
            ])
        );

        $this->assertFalse(
            $util->isInWhitelist('0.0.0.5', [
                '192.168.0.1/24',
            ])
        );
    }
}
