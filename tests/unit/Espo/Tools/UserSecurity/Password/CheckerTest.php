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

namespace tests\unit\Espo\Tools\UserSecurity\Password;

use Espo\Tools\UserSecurity\Password\Checker;
use Espo\Tools\UserSecurity\Password\ConfigProvider;
use PHPUnit\Framework\TestCase;

class CheckerTest extends TestCase
{
    public function testCheck(): void
    {
        $configProvider = $this->createMock(ConfigProvider::class);

        $configProvider
            ->expects($this->any())
            ->method('getStrengthLength')
            ->willReturn(6);

        $configProvider
            ->expects($this->any())
            ->method('getStrengthLetterCount')
            ->willReturn(2);

        $configProvider
            ->expects($this->any())
            ->method('getStrengthNumberCount')
            ->willReturn(1);

        $configProvider
            ->expects($this->any())
            ->method('getStrengthBothCases')
            ->willReturn(true);

        $configProvider
            ->expects($this->any())
            ->method('getStrengthSpecialCharacterCount')
            ->willReturn(1);

        $checker = new Checker($configProvider);

        $this->assertTrue($checker->checkStrength("Aa1:aaaaaaaaa"));
        $this->assertFalse($checker->checkStrength("Aa1aaaaaaaaa"));
        $this->assertFalse($checker->checkStrength("aa1:aaaaaaaaa"));
        $this->assertFalse($checker->checkStrength("aaa:aaaaaaaaa"));
        $this->assertFalse($checker->checkStrength("11:11111111"));
        $this->assertFalse($checker->checkStrength("Aa:1"));
    }
}
