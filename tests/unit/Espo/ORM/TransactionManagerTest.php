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

use Espo\ORM\QueryComposer\MysqlQueryComposer;
use Espo\ORM\TransactionManager;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TransactionManagerTest extends TestCase
{
    private $pdo;
    private $manager;

    protected function setUp() : void
    {
        $this->pdo = $this->createMock(PDO::class);
        $composer = $this->createMock(MysqlQueryComposer::class);

        $composer
            ->expects($this->any())
            ->method('composeCreateSavepoint')
            ->willReturnCallback(
                function ($name) {
                    return 'SAVEPOINT ' . $name;
                }
            );

        $composer
            ->expects($this->any())
            ->method('composeReleaseSavepoint')
            ->willReturnCallback(
                function ($name) {
                    return 'RELEASE SAVEPOINT ' . $name;
                }
            );

        $composer
            ->expects($this->any())
            ->method('composeRollbackToSavepoint')
            ->willReturnCallback(
                function ($name) {
                    return 'ROLLBACK TO SAVEPOINT ' . $name;
                }
            );

        $this->manager = new TransactionManager($this->pdo, $composer);
    }

    public function testStartOnce()
    {
        $this->pdo
            ->expects($this->once())
            ->method('beginTransaction');

        $this->manager->start();
    }

    public function testNested()
    {
        $this->pdo
            ->expects($this->exactly(1))
            ->method('beginTransaction');

        $invokedCount = $this->exactly(4);

        $this->pdo
            ->expects($invokedCount)
            ->method('exec')
            ->willReturnCallback(function ($sql) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('SAVEPOINT POINT_1', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('SAVEPOINT POINT_2', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 3) {
                    $this->assertEquals('RELEASE SAVEPOINT POINT_2', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 4) {
                    $this->assertEquals('ROLLBACK TO SAVEPOINT POINT_1', $sql);
                }

                return 1;
            });

       $this->pdo
            ->expects($this->exactly(1))
            ->method('commit');

        $this->manager->start();
        $this->manager->start();
        $this->manager->start();

        $this->manager->commit();
        $this->manager->rollback();
        $this->manager->commit();
    }

    public function testNestedRollback()
    {
        $invokedCount = $this->exactly(2);

        $this->pdo
            ->expects($invokedCount)
            ->method('exec')
            ->willReturnCallback(function ($sql) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('SAVEPOINT POINT_1', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('ROLLBACK TO SAVEPOINT POINT_1', $sql);
                }

                return 1;
            });

        $this->pdo
            ->expects($this->once())
            ->method('rollBack');

        $this->manager->start();
        $this->manager->start();

        $this->manager->rollback();
        $this->manager->rollback();
    }

    public function testLevel()
    {
        $this->assertEquals(0, $this->manager->getLevel());

        $this->assertFalse($this->manager->isStarted());

        $this->manager->start();

        $this->assertEquals(1, $this->manager->getLevel());

        $this->assertTrue($this->manager->isStarted());

        $this->manager->start();

        $this->assertEquals(2, $this->manager->getLevel());

        $this->manager->commit();

        $this->assertEquals(1, $this->manager->getLevel());

        $this->manager->rollback();

        $this->assertEquals(0, $this->manager->getLevel());

        $this->assertFalse($this->manager->isStarted());
    }

    public function testError1()
    {
        $this->expectException(RuntimeException::class);

        $this->manager->commit();
    }

    public function testError2()
    {
        $this->expectException(RuntimeException::class);

        $this->manager->start();

        $this->assertTrue($this->manager->isStarted());

        $this->manager->commit();
        $this->manager->rollback();
    }

    public function testRunOnce()
    {
        $this->pdo
            ->expects($this->once())
            ->method('beginTransaction');

        $this->pdo
            ->expects($this->once())
            ->method('commit');

        $this->manager->run(
            function () {}
        );
    }

    public function testRunNested()
    {
        $this->pdo
            ->expects($this->once())
            ->method('beginTransaction');

        $invokedCount = $this->exactly(2);

        $this->pdo
            ->expects($invokedCount)
            ->method('exec')
            ->willReturnCallback(function ($sql) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('SAVEPOINT POINT_1', $sql);
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('RELEASE SAVEPOINT POINT_1', $sql);
                }

                return 1;
            });

        $this->pdo
            ->expects($this->once())
            ->method('commit');

        $this->manager->run(
            function () {
                $this->manager->run(
                    function () {}
                );
            }
        );
    }

    public function testRunException()
    {
        $this->pdo
            ->expects($this->once())
            ->method('beginTransaction');

        $this->pdo
            ->expects($this->once())
            ->method('rollback');

        try {
            $this->manager->run(
                function () {
                    throw new RuntimeException();
                }
            );
        } catch (RuntimeException $e) {}
    }
}
