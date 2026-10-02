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

namespace tests\unit\Espo\Core\Authentication\AuthToken;

use Espo\Core\Authentication\AuthToken\Data;
use PHPUnit\Framework\TestCase;

class AuthTokenDataTest extends TestCase
{
    public function testCreate()
    {
        $authTokenData = Data::create([
            'passwordVersion' => 1,
            'ipAddress' => 'ip-address',
            'userId' => 'user-id',
            'portalId' => 'portal-id',
            'createSecret' => true,
        ]);

        $this->assertEquals(1, $authTokenData->getPasswordVersion());
        $this->assertEquals('ip-address', $authTokenData->getIpAddress());
        $this->assertEquals('user-id', $authTokenData->getUserId());
        $this->assertEquals('portal-id', $authTokenData->getPortalId());
        $this->assertTrue($authTokenData->toCreateSecret());
    }
}
