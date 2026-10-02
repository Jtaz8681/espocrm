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

namespace tests\unit\Espo\Core\Authentication\Logins\Oidc;

use Espo\Core\Acl\Cache\Clearer;
use Espo\Core\ApplicationState;
use Espo\Core\Authentication\Oidc\ConfigDataProvider;
use Espo\Core\Authentication\Oidc\UserProvider\DefaultUserInfoPopulator;
use Espo\Core\Authentication\Oidc\UserProvider\Sync;
use Espo\Core\Authentication\Oidc\UserProvider\UsernameValidator;
use Espo\Core\Authentication\Oidc\UserProvider\UserRepository;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\PasswordHash;

use PHPUnit\Framework\TestCase;

class SyncTest extends TestCase
{
    private ?Sync $sync = null;
    private ?Config $config = null;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $configDataProvider = $this->createMock(ConfigDataProvider::class);

        $populator = new DefaultUserInfoPopulator();

        $this->sync = new Sync(
            $this->createMock(UsernameValidator::class),
            $this->config,
            $configDataProvider,
            $this->createMock(UserRepository::class),
            $this->createMock(PasswordHash::class),
            $this->createMock(Clearer::class),
            $this->createMock(ApplicationState::class),
            $populator,
        );
    }

    public function testNormalizeUsername(): void
    {
        $this->config
            ->expects($this->any())
            ->method('get')
            ->with('userNameRegularExpression')
            ->willReturn('[^a-z0-9\-@_\.\s]');

        $this->assertEquals(
            'test_name',
            $this->sync->normalizeUsername('test_name')
        );

        $this->assertEquals(
            'test_name',
            $this->sync->normalizeUsername('test|name')
        );

        $this->assertEquals(
            'test@name',
            $this->sync->normalizeUsername('test@name')
        );

        $this->assertEquals(
            'test_name',
            $this->sync->normalizeUsername('test name')
        );
    }
}
