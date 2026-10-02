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

namespace tests\unit\Espo\Core\Acl\Map;

use Espo\Core\Acl\Map\MetadataProvider;
use Espo\Core\Acl\Permission;
use Espo\Core\Utils\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataProviderTest extends TestCase
{
    private $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->createMock(Metadata::class);
    }

    public function testGetPermissionList(): void
    {
        $provider = new MetadataProvider($this->metadata);

        $this->metadata
            ->expects($this->once())
            ->method('get')
            ->with(['app', 'acl', 'valuePermissionList'])
            ->willReturn(['assignmentPermission', 'portalPermission']);

        $this->assertEquals([Permission::ASSIGNMENT, 'portal'], $provider->getPermissionList());
    }
}
