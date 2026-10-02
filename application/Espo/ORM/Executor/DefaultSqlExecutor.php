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

namespace Espo\ORM\Executor;

use Espo\ORM\PDO\PDOProvider;
use Psr\Log\LoggerInterface;

use PDO;
use PDOStatement;
use PDOException;
use Exception;
use RuntimeException;

class DefaultSqlExecutor implements SqlExecutor
{
    private const MAX_ATTEMPT_COUNT = 4;

    private PDO $pdo;

    public function __construct(
        PDOProvider $pdoProvider,
        private ?LoggerInterface $logger = null,
        private bool $logAll = false,
        private bool $logFailed = false
    ) {
        $this->pdo = $pdoProvider->get();
    }

    /**
     * Execute a query.
     */
    public function execute(string $sql, bool $rerunIfDeadlock = false): PDOStatement
    {
        if ($this->logAll) {
            $this->logger?->info("SQL: " . $sql, ['isSql' => true]);
        }

        if (!$rerunIfDeadlock) {
            return $this->executeSqlWithDeadlockHandling($sql, 1);
        }

        return $this->executeSqlWithDeadlockHandling($sql);
    }

    private function executeSqlWithDeadlockHandling(string $sql, ?int $counter = null): PDOStatement
    {
        $counter = $counter ?? self::MAX_ATTEMPT_COUNT;

        try {
            $sth = $this->pdo->query($sql);
        } catch (Exception $e) {
            $counter--;

            if ($counter === 0 || !$this->isExceptionIsDeadlock($e)) {
                if ($this->logFailed) {
                    $this->logger?->error("SQL failed: " . $sql, ['isSql' => true]);
                }

                /** @var PDOException $e */
                throw $e;
            }

            return $this->executeSqlWithDeadlockHandling($sql, $counter);
        }

        if (!$sth) {
            throw new RuntimeException("Query execution failure.");
        }

        return $sth;
    }

    private function isExceptionIsDeadlock(Exception $e): bool
    {
        if (!$e instanceof PDOException) {
            return false;
        }

        return isset($e->errorInfo) && $e->errorInfo[0] == 40001 && $e->errorInfo[1] == 1213;
    }
}
