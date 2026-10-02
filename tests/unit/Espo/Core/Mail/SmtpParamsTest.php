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

namespace tests\unit\Espo\Core\Mail;

use Espo\Core\Mail\SmtpParams;
use PHPUnit\Framework\TestCase;

class SmtpParamsTest extends TestCase
{
    public function testFromArray1(): void
    {
        $array = [
            'server' => 'localhost',
            'port' => 587,
            'fromAddress' => 'test@test',
            'fromName' => 'name',
            'auth' => false,
        ];

        $this->assertEquals($array, SmtpParams::fromArray($array)->toArray());
    }

    public function testFromArray2(): void
    {
        $array = [
            'server' => 'localhost',
            'port' => 587,
            'fromAddress' => 'test@test',
            'fromName' => 'name',
            'auth' => true,
            'connectionOptions' => ['test' => 'test'],
            'authMechanism' => 'login',
            'username' => 'tester',
            'password' => 'password',
            'security' => 'ssl',
        ];

        $this->assertEquals($array, SmtpParams::fromArray($array)->toArray());
    }

    public function testBuilding(): void
    {
        $params = SmtpParams::create('localhost', 587)
            ->withFromAddress('test@test')
            ->withFromName('name')
            ->withAuth()
            ->withUsername('tester')
            ->withPassword('test')
            ->withAuthMechanism('login')
            ->withConnectionOptions(['test' => 'test']);

        $this->assertEquals('localhost', $params->getServer());
        $this->assertEquals(587, $params->getPort());

        $this->assertEquals('test@test', $params->getFromAddress());
        $this->assertEquals('name', $params->getFromName());

        $this->assertEquals(true, $params->useAuth());

        $this->assertEquals('tester', $params->getUsername());
        $this->assertEquals('test', $params->getPassword());
        $this->assertEquals(['test' => 'test'], $params->getConnectionOptions());
    }
}
