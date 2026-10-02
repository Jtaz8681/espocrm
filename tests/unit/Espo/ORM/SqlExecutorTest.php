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

use Espo\ORM\Executor\DefaultSqlExecutor;
use Espo\ORM\PDO\PDOProvider;

use PDO;
use PDOStatement;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SqlExecutorTest extends TestCase
{
    private $pdo;
    private $sth;
    private $executor;

    protected function setUp() : void
    {
        $this->pdo = $this->createMock(PDO::class);

        $pdoProvider = $this->createMock(PDOProvider::class);
        $pdoProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->pdo);

        $this->sth = $this->getMockBuilder(PDOStatement::class)->disableOriginalConstructor()->getMock();

        $this->executor = new DefaultSqlExecutor($pdoProvider);
    }

    public function testExecute1()
    {
        $sql = "SOME QUERY";

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->sth)
            ->with($sql);

        $sth = $this->executor->execute($sql);

        $this->assertInstanceOf(PDOStatement::class, $sth);
    }

    public function testExecuteException1()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql);
        } catch (PDOException $e) {

        }
    }

    public function testExecuteException2()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql, true);
        } catch (PDOException $e) {

        }
    }

    public function testExecuteDeadlock1()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql);
        } catch (PDOException) {}
    }

    public function testExecuteDeadlock2()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [40001, 1213];

        $invokedCount = $this->exactly(2);

        $this->pdo
            ->expects($invokedCount)
            ->method('query')
            ->willReturnCallback(function () use ($invokedCount, $e) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    throw $e;
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    return $this->sth;
                }

                throw new RuntimeException();
            });

        $sth = $this->executor->execute($sql, true);

        $this->assertInstanceOf(PDOStatement::class, $sth);
    }
}
