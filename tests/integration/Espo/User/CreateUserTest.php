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

class CreateUserTest extends BaseTestCase
{
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

    public function testCreateUser()
    {
        $newUser = $this->createUser('tester');

        $this->assertInstanceOf('Espo\\ORM\\Entity', $newUser);
        $this->assertTrue(!empty($newUser->getId()));
        $this->assertEquals('tester', $newUser->get('userName'));
    }

    public function testCreateUserWithAttributes()
    {
        $newUser = $this->createUser([
            'userName' => 'tester',
            'firstName' => 'Test',
            'lastName' => 'Tester',
            'emailAddress' => 'test@tester.com',
        ]);

        $this->assertInstanceOf('Espo\\ORM\\Entity', $newUser);
        $this->assertTrue(!empty($newUser->getId()));
        $this->assertEquals('tester', $newUser->get('userName'));
        $this->assertEquals('Test', $newUser->get('firstName'));
        $this->assertEquals('Tester', $newUser->get('lastName'));
        $this->assertEquals('test@tester.com', $newUser->get('emailAddress'));
    }

    public function testCreateUserWithRole()
    {
        $newUser = $this->createUser('tester', [
            'assignmentPermission' => 'team',
            'userPermission' => 'team',
            'portalPermission' => 'not-set',
            'data' =>
            [
                'Account' => false,
                'Call' =>
                [
                    'create' => 'yes',
                    'read' => 'team',
                    'edit' => 'team',
                    'delete' => 'no',
                ],
            ],
            'fieldData' =>
            [
                'Call' =>
                [
                    'direction' =>
                    [
                        'read' => 'yes',
                        'edit' => 'no',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf('Espo\\ORM\\Entity', $newUser);
        $this->assertTrue(!empty($newUser->getId()));
        $this->assertEquals('tester', $newUser->get('userName'));
    }

    public function testCreatePortalUserWithRole()
    {
        $newUser = $this->createUser([
                'userName' => 'tester',
                'lastName' => 'tester',
                'portalsIds' => [
                    'testPortalId',
                ],
        ], [
            'assignmentPermission' => 'team',
            'userPermission' => 'team',
            'portalPermission' => 'not-set',
            'data' => [
                'Account' => false,
            ],
            'fieldData' => [
                'Call' => [
                    'direction' => [
                        'read' => 'yes',
                        'edit' => 'no',
                    ],
                ],
            ],
        ], true);

        $this->assertInstanceOf('Espo\\ORM\\Entity', $newUser);
        $this->assertTrue(!empty($newUser->getId()));
        $this->assertEquals('tester', $newUser->get('userName'));
    }
}
