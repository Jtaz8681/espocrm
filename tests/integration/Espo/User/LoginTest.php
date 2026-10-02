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

namespace tests\integration\Espo\User;

use Espo\Entities\User;
use tests\integration\Core\BaseTestCase;

class LoginTest extends BaseTestCase
{
    protected ?string $password = '1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUser([
            'type' => User::TYPE_ADMIN,
            'userName' => 'admin',
            'lastName' => 'Admin',
        ]);

        $this->authenticate('admin');
    }

    public function testLogin(): void
    {
        $this->authenticate('admin');

        $user = $this->getContainer()->getByClass(User::class);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->getUserName());
    }

    public function testWrongCredentials(): void
    {
        $this->auth('admin', 'wrong-password');

        $application = $this->createApplication(reuse: true);

        $this->assertFalse($application->getContainer()->has('user'));
    }
}
