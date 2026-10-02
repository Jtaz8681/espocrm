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

namespace Espo\ORM\Repository;

use Espo\ORM\TransactionManager;

use RuntimeException;

/**
 * Wrapper for TransactionManager to be used within RDBRepository in beforeSave and afterSave methods.
 */
class RDBTransactionManager
{
    private int $level = 0;

    public function __construct(private TransactionManager $transactionManager)
    {}

    public function isStarted(): bool
    {
        return $this->level > 0;
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            throw new RuntimeException("Can't start a transaction more than once.");
        }

        $this->transactionManager->start();

        $this->level = $this->transactionManager->getLevel();
    }

    public function commit(): void
    {
        if (!$this->isStarted()) {
            throw new RuntimeException("Can't commit not started transaction.");
        }

        while ($this->transactionManager->getLevel() >= $this->level) {
            $this->transactionManager->commit();
        }

        $this->level = 0;
    }

    public function rollback(): void
    {
        if (!$this->isStarted()) {
            throw new RuntimeException("Can't rollback not started transaction.");
        }

        while ($this->transactionManager->getLevel() >= $this->level) {
            $this->transactionManager->rollback();
        }

        $this->level = 0;
    }
}
