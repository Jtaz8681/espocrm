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

namespace tests\unit\Espo\ORM;

use Espo\ORM\EntityFactory;
use Espo\ORM\Locker\MysqlLocker;
use Espo\ORM\Metadata;
use Espo\ORM\QueryComposer\MysqlQueryComposer;
use Espo\ORM\TransactionManager;

use PDO;
use PHPUnit\Framework\TestCase;

class MysqlLockerTest extends TestCase
{
    private $locker;
    private $pdo;

    protected function setUp() : void
    {
        $this->pdo = $this->createMock(PDO::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $metadata = $this->createMock(Metadata::class);
        $transactionManager = $this->createMock(TransactionManager::class);

        $composer = new MysqlQueryComposer($this->pdo, $entityFactory, $metadata);
        $this->locker = new MysqlLocker($this->pdo, $composer, $transactionManager);
    }

    public function testLockCommit()
    {
        $invokedCount = $this->exactly(3);

        $this->pdo
            ->expects($invokedCount)
            ->method('exec')
            ->willReturnCallback(function ($sql) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('LOCK TABLES `account` WRITE', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('LOCK TABLES `contact` READ', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 3) {
                    $this->assertEquals('UNLOCK TABLES', $sql);
                }

                return 1;
            });

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->commit();

        $this->assertFalse($this->locker->isLocked());
    }

    public function testLockRollback()
    {
        $invokedCount = $this->exactly(3);

        $this->pdo
            ->expects($invokedCount)
            ->method('exec')
            ->willReturnCallback(function ($sql) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('LOCK TABLES `account` WRITE', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('LOCK TABLES `contact` READ', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 3) {
                    $this->assertEquals('UNLOCK TABLES', $sql);
                }

                return 1;
            });

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->commit();

        $this->assertFalse($this->locker->isLocked());
    }
}
