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

namespace tests\integration\Espo\Core\Authentication\AuthToken;

use Espo\Core\Authentication\AuthToken\Data;
use Espo\Core\Authentication\AuthToken\Manager;
use tests\integration\Core\BaseTestCase;

class AuthTokenManagerTest extends BaseTestCase
{
    public function testCreateWithSecret()
    {
        $authTokenManager = $this->getContainer()->getByClass(Manager::class);

        $authTokenData = Data::create([
            'passwordVersion' => 1,
            'ipAddress' => 'ip-address',
            'userId' => 'user-id',
            'portalId' => 'portal-id',
            'createSecret' => true,
        ]);

        $authToken = $authTokenManager->create($authTokenData);

        $this->assertEquals($authTokenData->getPasswordVersion(), $authToken->getPasswordVersion());
        $this->assertEquals($authTokenData->getUserId(), $authToken->getUserId());
        $this->assertEquals($authTokenData->getPortalId(), $authToken->getPortalId());

        $this->assertTrue($authToken->isActive());

        $this->assertNotEmpty($authToken->getToken());
        $this->assertNotEmpty($authToken->getSecret());

        $this->assertNotEmpty($authToken->get('lastAccess'));
    }

    public function testCreateWithNoSecretNoPortal()
    {
        $authTokenManager = $this->getContainer()->getByClass(Manager::class);

        $authTokenData = Data::create([
            'passwordVersion' => 1,
            'ipAddress' => 'ip-address',
            'userId' => 'user-id',
            'portalId' => null,
            'createSecret' => false,
        ]);

        $authToken = $authTokenManager->create($authTokenData);

        $this->assertEquals($authTokenData->getPasswordVersion(), $authToken->getPasswordVersion());
        $this->assertEquals($authTokenData->getUserId(), $authToken->getUserId());

        $this->assertEmpty($authToken->getPortalId());

        $this->assertNotEmpty($authToken->getToken());
        $this->assertEmpty($authToken->getSecret());
    }

    public function testRenew()
    {
        $authTokenManager = $this->getContainer()->getByClass(Manager::class);

        $authTokenData = Data::create([
            'passwordVersion' => 1,
            'userId' => 'user-id',
        ]);

        $authToken = $authTokenManager->create($authTokenData);

        $authToken = $authTokenManager->get($authToken->getToken());

        $authTokenManager->renew($authToken);

        $this->assertNotEmpty($authToken->get('lastAccess'));
    }

    public function testInactivate()
    {
        $authTokenManager = $this->getContainer()->getByClass(Manager::class);

        $authTokenData = Data::create([
            'passwordVersion' => 1,
            'userId' => 'user-id',
        ]);

        $authToken = $authTokenManager->create($authTokenData);

        $authToken = $authTokenManager->get($authToken->getToken());

        $authTokenManager->inactivate($authToken);

        $this->assertFalse($authToken->isActive());
    }
}
