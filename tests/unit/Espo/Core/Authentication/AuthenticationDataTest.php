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

namespace tests\unit\Espo\Core\Authentication;

use Espo\Core\Authentication\AuthenticationData;

class AuthenticationDataTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate1(): void
    {
        $data = AuthenticationData::create()
            ->withUsername('u')
            ->withPassword('p')
            ->withMethod(null);

        $this->assertEquals('u', $data->getUsername());
        $this->assertEquals('p', $data->getPassword());
        $this->assertNull($data->getMethod());
    }

    public function testCreate2(): void
    {
        $data = AuthenticationData::create()
            ->withUsername(null)
            ->withPassword(null)
            ->withMethod('m');

        $this->assertNull($data->getUsername());
        $this->assertNull($data->getPassword());
        $this->assertEquals('m', $data->getMethod());
    }
}
