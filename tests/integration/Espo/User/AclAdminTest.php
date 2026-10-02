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

use Espo\Core\Api\ControllerActionProcessor;
use Espo\Core\Api\ResponseWrapper;
use tests\integration\Core\BaseTestCase;

class AclAdminTest extends BaseTestCase
{
    public function testCreateUser()
    {
        $this->createUser([
            'userName' => 'admin-test',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-test');

        $processor = $this->getInjectableFactory()
            ->create(ControllerActionProcessor::class);

        $data = [
            'userName' => 'test',
            'lastName' => 'Test',
            'password' => '1',
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $response = $this->createMock(ResponseWrapper::class);

        $response
            ->expects($this->once())
            ->method('writeBody');

        $processor->process('User', 'create', $request, $response);
    }

    public function testCreateTeam()
    {
        $this->createUser([
            'userName' => 'admin-test',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-test');

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $data = [
            'name' => 'test',
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $response = $this->createMock(ResponseWrapper::class);

        $response
            ->expects($this->once())
            ->method('writeBody');

        $processor->process('Team', 'create', $request, $response);
    }

    public function testCreateRole()
    {
        $this->createUser([
            'userName' => 'admin-test',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-test');

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $data = [
            'name' => 'test',
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $response = $this->createMock(ResponseWrapper::class);

        $response
            ->expects($this->once())
            ->method('writeBody');

        $processor->process('Role', 'create', $request, $response);
    }

    public function testCreatePortal()
    {
        $this->createUser([
            'userName' => 'admin-test',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-test');

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $data = [
            'name' => 'test',
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $response = $this->createMock(ResponseWrapper::class);

        $response
            ->expects($this->once())
            ->method('writeBody');

        $processor->process('Portal', 'create', $request, $response);
    }
}
