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

namespace tests\unit\Espo\Core;

use Espo\Core\Acl\AccessChecker\AccessCheckerFactory;
use Espo\Core\Acl\GlobalRestriction;
use Espo\Core\Acl\Map\MapFactory;
use Espo\Core\Acl\OwnershipChecker\OwnershipCheckerFactory;
use Espo\Core\Acl\OwnerUserFieldProvider;
use Espo\Core\Acl\Permission;
use Espo\Core\Acl\Table;
use Espo\Core\Acl\Table\TableFactory;
use Espo\Core\AclManager;
use Espo\Core\ORM\EntityManager;

use Espo\Entities\User;
use PHPUnit\Framework\TestCase;

class AclManagerTest extends TestCase
{
    /** @var AclManager */
    private $aclManager;
    /** @var TableFactory */
    private $tableFactory;
    /** @var User */
    private $user;

    private $table;

    protected function setUp(): void
    {
        $this->user = $this->createMock(User::class);
        $this->table = $this->createMock(Table::class);

        $accessCheckerFactory = $this->createMock(AccessCheckerFactory::class);
        $ownershipCheckerFactory = $this->createMock(OwnershipCheckerFactory::class);
        $this->tableFactory = $this->createMock(TableFactory::class);
        $mapFactory = $this->createMock(MapFactory::class);
        $globalRestriction = $this->createMock(GlobalRestriction::class);

        $this->aclManager = new AclManager(
            $accessCheckerFactory,
            $ownershipCheckerFactory,
            $this->tableFactory,
            $mapFactory,
            $globalRestriction,
            $this->createMock(OwnerUserFieldProvider::class),
            $this->createMock(EntityManager::class)
        );
    }

    private function initTableFactory(User $user, Table $table): void
    {
        $this->tableFactory
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn($table);
    }

    public function testGetPermissionLevel1(): void
    {
        $this->initTableFactory($this->user, $this->table);

        $this->table
            ->expects($this->once())
            ->method('getPermissionLevel')
            ->with(Permission::ASSIGNMENT)
            ->willReturn(Table::LEVEL_YES);

        $this->aclManager->getPermissionLevel($this->user, Permission::ASSIGNMENT);
    }

    public function testGetPermissionLevel2(): void
    {
        $this->initTableFactory($this->user, $this->table);

        $this->table
            ->expects($this->once())
            ->method('getPermissionLevel')
            ->with(Permission::ASSIGNMENT)
            ->willReturn(Table::LEVEL_YES);

        $this->aclManager->getPermissionLevel($this->user, Permission::ASSIGNMENT);
    }
}
