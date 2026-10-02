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

namespace tests\unit\Espo\Core\Select\AccessControl;

use Espo\Core\Acl;
use Espo\Core\Portal\Acl as AclPortal;
use Espo\Core\Select\AccessControl\DefaultFilterResolver;
use Espo\Core\Select\AccessControl\DefaultPortalFilterResolver;

use PHPUnit\Framework\TestCase;

class FilterResolverTest extends TestCase
{
    private $acl;
    private $aclPortal;
    private $entityType;
    private $resolver;

    protected function setUp(): void
    {
        $this->acl = $this->createMock(Acl::class);
        $this->aclPortal = $this->createMock(AclPortal::class);

        $this->entityType = 'Test';
    }

    public function testResolveRegularOnlyOwn()
    {
        $this->assertEquals(
            'onlyOwn',
            $this->initResolveTest(false, 'checkReadOnlyOwn')
        );
    }

    public function testResolveRegularOnlyTeam()
    {
        $this->assertEquals(
            'onlyTeam',
            $this->initResolveTest(false, 'checkReadOnlyTeam')
        );
    }

    public function testResolveRegularNo()
    {
        $this->assertEquals(
            'no',
            $this->initResolveTest(false, 'checkReadNo')
        );
    }

    public function testResolvePortalOnlyOwn()
    {
        $this->assertEquals(
            'portalOnlyOwn',
            $this->initResolveTest(true, 'checkReadOnlyOwn')
        );
    }

    public function testResolvePortalOnlyAccount()
    {
        $this->assertEquals(
            'portalOnlyAccount',
            $this->initResolveTest(true, 'checkReadOnlyAccount')
        );
    }

    public function testResolvePortalOnlyContact()
    {
        $this->assertEquals(
            'portalOnlyContact',
            $this->initResolveTest(true, 'checkReadOnlyContact')
        );
    }

    public function testResolvePortalNo()
    {
        $this->assertEquals(
            'no',
            $this->initResolveTest(true, 'checkReadNo')
        );
    }

    public function testResolveAll()
    {
        $this->assertEquals(
            'all',
            $this->initResolveTest(false, 'checkReadAll')
        );
    }

    public function testResolvePortalAll()
    {
        $this->assertEquals(
            'portalAll',
            $this->initResolveTest(true, 'checkReadAll')
        );
    }

    protected function initResolveTest(bool $isPortal = false, ?string $method = null): ?string
    {
        $acl = $this->acl;

        if ($isPortal) {
            $acl = $this->aclPortal;
        }

        if (!$isPortal) {
            $this->resolver = new DefaultFilterResolver(
                $this->entityType,
                $acl
            );
        }

        if ($isPortal) {
            $this->resolver = new DefaultPortalFilterResolver(
                $this->entityType,
                $acl
            );
        }

        if ($method) {
            $acl
                ->expects($this->any())
                ->method($method)
                ->with($this->entityType)
                ->willReturn(true);
        }

        return $this->resolver->resolve();
    }
}
