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

namespace tests\unit\Espo\ORM\Repository;

require_once 'tests/unit/testData/DB/Entities.php';

use Espo\ORM\Repository\RDBTransactionManager;
use Espo\ORM\TransactionManager;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class RDBTransactionManagerTest extends TestCase
{
    private $wrappee;
    private $manager;

    protected function setUp(): void
    {
        $this->wrappee = $this->createMock(TransactionManager::class);

        $this->manager = new RDBTransactionManager($this->wrappee);
    }

    public function testStartOnce()
    {

        $this->wrappee
            ->expects($this->once())
            ->method('start');

        $this->manager->start();
    }

    public function testException()
    {
        $this->wrappee
            ->expects($this->once())
            ->method('start');

        $this->wrappee
            ->expects($this->once())
            ->method('getLevel')
            ->willReturn(1);

        $this->expectException(RuntimeException::class);

        $this->manager->start();

        $this->manager->start();
    }

    public function testCommit()
    {
        $this->wrappee
            ->expects($this->once())
            ->method('start');

        $this->wrappee
            ->expects($this->exactly(4))
            ->method('getLevel')
            ->willReturnOnConsecutiveCalls(1, 2, 1, 0);

        $this->wrappee
            ->expects($this->exactly(2))
            ->method('commit');

        $this->manager->start();

        $this->manager->commit();
    }
}
