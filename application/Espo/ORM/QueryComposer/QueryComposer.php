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

namespace Espo\ORM\QueryComposer;

use Espo\ORM\Query\Select as SelectQuery;
use Espo\ORM\Query\Update as UpdateQuery;
use Espo\ORM\Query\Insert as InsertQuery;
use Espo\ORM\Query\Delete as DeleteQuery;
use Espo\ORM\Query\Union as UnionQuery;
use Espo\ORM\Query\LockTable as LockTableQuery;

interface QueryComposer
{
    public function composeSelect(SelectQuery $query): string;

    public function composeUpdate(UpdateQuery $query): string;

    public function composeDelete(DeleteQuery $query): string;

    public function composeInsert(InsertQuery $query): string;

    public function composeUnion(UnionQuery $query): string;

    public function composeLockTable(LockTableQuery $query): string;

    public function composeCreateSavepoint(string $savepointName): string;

    public function composeReleaseSavepoint(string $savepointName): string;

    public function composeRollbackToSavepoint(string $savepointName): string;
}
