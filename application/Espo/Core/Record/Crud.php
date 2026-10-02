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

namespace Espo\Core\Record;

use Espo\ORM\Entity;

use stdClass;

/**
 * @template TEntity of Entity
 */
interface Crud
{
    /**
     * Create a record.
     *
     * @return CreateResult<TEntity>
     */
    public function create(stdClass $data, CreateParams $params): CreateResult;

    /**
     * Read a record.
     *
     * @return ReadResult<TEntity>
     */
    public function read(string $id, ReadParams $params): ReadResult;

    /**
     * Update a record.
     *
     * @return UpdateResult<TEntity>
     */
    public function update(string $id, stdClass $data, UpdateParams $params): UpdateResult;

    /**
     * Delete a record.
     */
    public function delete(string $id, DeleteParams $params): DeleteResult;
}
