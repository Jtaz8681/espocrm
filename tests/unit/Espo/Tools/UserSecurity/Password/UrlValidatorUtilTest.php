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

use Espo\Tools\UserSecurity\Password\Recovery\UrlValidatorUtil;
use PHPUnit\Framework\TestCase;

class UrlValidatorUtilTest extends TestCase
{
    public function testValidate(): void
    {
        $this->assertTrue(
            UrlValidatorUtil::validate('https://test.com', 'https://test.com')
        );

        $this->assertTrue(
            UrlValidatorUtil::validate('https://test.com/test', 'https://test.com')
        );

        $this->assertTrue(
            UrlValidatorUtil::validate('https://test.com/test', 'https://test.com/test')
        );

        $this->assertFalse(
            UrlValidatorUtil::validate('https://test.com.test', 'https://test.com')
        );

        $this->assertFalse(
            UrlValidatorUtil::validate('https://test.com.test<test', 'https://test.com')
        );
    }
}
