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

/**
 * Locks and unlocks tables.
 * Wraps operations between lock and unlock into a transaction.
 */
interface Locker
{
    /**
     * Whether any table has been locked.
     */
    public function isLocked(): bool;

    /**
     * Locks a table in an exclusive mode. Starts a transaction on first call.
     */
    public function lockExclusive(string $entityType): void;

    /**
     * Locks a table in a share mode. Starts a transaction on first call.
     */
    public function lockShare(string $entityType): void;

    /**
     * Commits changes and unlocks tables.
     */
    public function commit(): void;

    /**
     * Rollbacks changes and unlocks tables.
     */
    public function rollback(): void;
}
