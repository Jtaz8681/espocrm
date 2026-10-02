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
use Espo\ORM\Locker\BaseLocker;
use Espo\ORM\Metadata;
use Espo\ORM\QueryComposer\MysqlQueryComposer;
use Espo\ORM\TransactionManager;

use PDO;
use PHPUnit\Framework\TestCase;

class BaseLockerTest extends TestCase
{
    private $transactionManager;
    private $locker;
    private $pdo;

    protected function setUp() : void
    {
        $this->pdo = $this->createMock(PDO::class);

        $entityFactory = $this->createMock(EntityFactory::class);

        $metadata = $this->getMockBuilder(Metadata::class)->disableOriginalConstructor()->getMock();

        $this->transactionManager = $this->getMockBuilder(TransactionManager::class)
            ->disableOriginalConstructor()->getMock();

        $composer = new MysqlQueryComposer($this->pdo, $entityFactory, $metadata);

        $this->locker = new BaseLocker($this->pdo, $composer, $this->transactionManager);
    }

    public function testLockCommit()
    {
        $this->transactionManager
            ->expects($this->exactly(2))
            ->method('start');

        $invokedCount = $this->exactly(2);

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

                return 1;
            });

        $this->transactionManager
            ->expects($this->once())
            ->method('commit');

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->commit();

        $this->assertFalse($this->locker->isLocked());
    }

    public function testLockRollback()
    {
        $this->transactionManager
            ->expects($this->exactly(2))
            ->method('start');

        $invokedCount = $this->exactly(2);

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

                return 1;
            });

        $this->transactionManager
            ->expects($this->once())
            ->method('rollback');

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->rollback();

        $this->assertFalse($this->locker->isLocked());
    }
}
