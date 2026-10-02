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

namespace Espo\ORM\Locker;

use Espo\ORM\Query\LockTableBuilder;
use Espo\ORM\QueryComposer\QueryComposer;
use Espo\ORM\TransactionManager;

use PDO;
use RuntimeException;

class BaseLocker implements Locker
{
    private bool $isLocked = false;

    public function __construct(
        private PDO $pdo,
        private QueryComposer $queryComposer,
        private TransactionManager $transactionManager
    ) {}

    /**
     * {@inheritdoc}
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function lockExclusive(string $entityType): void
    {
        $this->isLocked = true;

        $this->transactionManager->start();

        $query = (new LockTableBuilder())
            ->table($entityType)
            ->inExclusiveMode()
            ->build();

        $sql = $this->queryComposer->composeLockTable($query);

        $this->pdo->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function lockShare(string $entityType): void
    {
        $this->isLocked = true;

        $this->transactionManager->start();

        $query = (new LockTableBuilder())
            ->table($entityType)
            ->inShareMode()
            ->build();

        $sql = $this->queryComposer->composeLockTable($query);

        $this->pdo->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        if (!$this->isLocked) {
            throw new RuntimeException("Can't commit, it was not locked.");
        }

        $this->transactionManager->commit();

        $this->isLocked = false;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): void
    {
        if (!$this->isLocked) {
            throw new RuntimeException("Can't rollback, it was not locked.");
        }

        $this->transactionManager->rollback();

        $this->isLocked = false;
    }
}
