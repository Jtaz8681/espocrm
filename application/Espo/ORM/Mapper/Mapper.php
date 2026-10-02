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

namespace Espo\ORM\Mapper;

use Espo\ORM\Entity;
use Espo\ORM\Collection;
use Espo\ORM\Query\Select;

interface Mapper
{
    /**
     * Get a first entity from DB.
     */
    public function selectOne(Select $select): ?Entity;

    /**
     * Select entities from DB.
     *
     * @return Collection<Entity>
     */
    public function select(Select $select): Collection;

    /**
     * Get a number of records in DB.
     */
    public function count(Select $select): int;

    /**
     * Insert an entity into DB.
     */
    public function insert(Entity $entity): void;

    /**
     * Insert a collection into DB.
     *
     * @param Collection<Entity> $collection
     */
    public function massInsert(Collection $collection): void;

    /**
     * Update an entity in DB.
     */
    public function update(Entity $entity): void;

    /**
     * Delete an entity from DB or mark as deleted.
     */
    public function delete(Entity $entity): void;

    /**
     * Insert an entity into DB, on duplicate key update specified attributes.
     *
     * @param string[] $onDuplicateUpdateAttributeList
     */
    public function insertOnDuplicateUpdate(Entity $entity, array $onDuplicateUpdateAttributeList): void;
}
