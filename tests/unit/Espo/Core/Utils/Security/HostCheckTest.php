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

namespace tests\unit\Espo\Core\Utils\Security;

use Espo\Core\Utils\Security\HostCheck;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HostCheckTest extends TestCase
{
    public function testIsHostAndNotInternal(): void
    {
        $hostCheck = new HostCheck();

        $this->assertTrue(
            $hostCheck->isHostAndNotInternal('200.1.1.1')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('172.20.0.1')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('0177.0.0.1')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('127.1')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('127.0.1')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('2130706433')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('0x7f000001')
        );

        $this->assertFalse(
            $hostCheck->isHostAndNotInternal('0x7f.1')
        );
    }

    public static function ipAddressTestProvider(): array
    {
        return [
            ['200.1.1.1', true],
            ['172.20.0.1', false],
            ['2606:2800:220:1:248:1893:25c8:1946', true],
            ['::ffff:127.0.0.1', false],
            ['64:ff9b::a00:1', false],
            ['2002:0a00:0001::', false],
            ['2001:0000:4136:e378:8000:0000:f5ff:fffe', false],
            ['2001:4860:4860::8888', true],
            ['2606:4700:4700::1111', true],
            ['2620:fe::fe', true],
            ['2404:6800:4004:80c::200e', true],
            ['2a09:bac1:1234::1', true],
            ['fe80::1', false],
            ['fc00::1', false],
            ['::1', false],
        ];
    }

    #[DataProvider('ipAddressTestProvider')]
    public function testIpAddress(string $ipAddress, bool $expected)
    {
        $hostCheck = new HostCheck();

        $this->assertEquals($expected, $hostCheck->ipAddressIsNotInternal($ipAddress));
    }
}
