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

namespace tests\unit\Espo\Core\Utils\Security;

use Espo\Core\Utils\Security\HostCheck;
use Espo\Core\Utils\Security\UrlCheck;
use PHPUnit\Framework\TestCase;

class UrlCheckTest extends TestCase
{
    public function testGetCurlResolveDual(): void
    {
        $hostCheck = $this->createMock(HostCheck::class);

        $urlCheck = new UrlCheck($hostCheck);

        $url = 'https://test.com';

        $hostCheck->method('isDomainHost')
            ->with('test.com')
            ->willReturn(true);

        $hostCheck->method('getHostIpAddresses')
            ->with('test.com')
            ->willReturn([
                '10.0.0.1',
                '10.0.0.2',
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ]);

        $this->assertEquals([
            'test.com:443:10.0.0.1',
            'test.com:443:10.0.0.2',
        ], $urlCheck->getCurlResolve($url));
    }

    public function testGetCurlResolveIpV6Only(): void
    {
        $hostCheck = $this->createMock(HostCheck::class);

        $urlCheck = new UrlCheck($hostCheck);

        $url = 'https://test.com';

        $hostCheck->method('isDomainHost')
            ->with('test.com')
            ->willReturn(true);

        $hostCheck->method('getHostIpAddresses')
            ->with('test.com')
            ->willReturn([
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ]);

        $this->assertEquals([
            'test.com:443:[2001:0db8:85a3:0000:0000:8a2e:0370:7334]',
        ], $urlCheck->getCurlResolve($url));
    }
}
