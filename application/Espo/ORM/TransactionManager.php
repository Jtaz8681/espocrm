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

namespace Espo\ORM;

use Espo\ORM\QueryComposer\QueryComposer;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;
use Closure;

class TransactionManager
{
    private int $level = 0;

    public function __construct(private PDO $pdo, private QueryComposer $queryComposer)
    {}

    /**
     * Whether a transaction is started.
     */
    public function isStarted(): bool
    {
        return $this->level > 0;
    }

    /**
     * Get a current nesting level.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Run a function in a transaction. Commits if success, rolls back if an exception occurs.
     *
     * @return mixed A function result.
     */
    public function run(Closure $function)
    {
        $this->start();

        try {
            $result = $function();

            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();

            /**
             * @var PDOException $e
             */
            throw $e;
        }

        return $result;
    }

    /**
     * Start a transaction.
     */
    public function start(): void
    {
        if ($this->level > 0) {
            $this->createSavepoint();

            $this->level++;

            return;
        }

        $this->pdo->beginTransaction();

        $this->level++;
    }

    /**
     * Commit a transaction.
     */
    public function commit(): void
    {
        if ($this->level === 0) {
            throw new RuntimeException("Can't commit not started transaction.");
        }

        $this->level--;

        if ($this->level > 0) {
            $this->releaseSavepoint();

            return;
        }

        $this->pdo->commit();
    }

    /**
     * Rollback a transaction.
     */
    public function rollback(): void
    {
        if ($this->level === 0) {
            throw new RuntimeException("Can't rollback not started transaction.");
        }

        $this->level--;

        if ($this->level > 0) {
            $this->rollbackToSavepoint();

            return;
        }

        $this->pdo->rollBack();
    }

    private function getCurrentSavepoint(): string
    {
        return 'POINT_' . (string) $this->level;
    }

    private function createSavepoint(): void
    {
        $sql = $this->queryComposer->composeCreateSavepoint($this->getCurrentSavepoint());

        $this->pdo->exec($sql);
    }

    private function releaseSavepoint(): void
    {
        $sql = $this->queryComposer->composeReleaseSavepoint($this->getCurrentSavepoint());

        $this->pdo->exec($sql);
    }

    private function rollbackToSavepoint(): void
    {
        $sql = $this->queryComposer->composeRollbackToSavepoint($this->getCurrentSavepoint());

        $this->pdo->exec($sql);
    }
}
