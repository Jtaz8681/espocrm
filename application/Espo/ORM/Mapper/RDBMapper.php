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

use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\Query\Select;

interface RDBMapper extends Mapper
{
    /**
     * Relate an entity with another entity.
     *
     * @param Entity $entity An entity.
     * @param string $relationName A relation name.
     * @param Entity $foreignEntity A foreign entity.
     * @param array<string, mixed>|null $columnData Column values.
     * @return bool True if the row was affected.
     */
    public function relate(Entity $entity, string $relationName, Entity $foreignEntity, ?array $columnData): bool;

    /**
     * Unrelate an entity from another entity.
     *
     * @param Entity $entity An entity.
     * @param string $relationName A relation name.
     * @param Entity $foreignEntity A foreign entity.
     */
    public function unrelate(Entity $entity, string $relationName, Entity $foreignEntity): void;

    /**
     * Relate an entity from another entity by a given ID.
     *
     * @param Entity $entity An entity.
     * @param string $relationName A relation name.
     * @param string $id A foreign ID.
     * @param array<string, mixed>|null $columnData Column values.
     */
    public function relateById(Entity $entity, string $relationName, string $id, ?array $columnData = null): bool;

    /**
     * Unrelate an entity from another entity by a given ID.
     *
     * @param Entity $entity An entity.
     * @param string $relationName A relation name.
     * @param string $id A foreign ID.
     */
    public function unrelateById(Entity $entity, string $relationName, string $id): void;

    /**
     * Mass relate.
     */
    public function massRelate(Entity $entity, string $relationName, Select $select): void;

    /**
     * Update relationship columns.
     *
     * @param array<string, mixed> $columnData
     */
    public function updateRelationColumns(
        Entity $entity,
        string $relationName,
        string $id,
        array $columnData
    ): void;

    /**
     * Get a relationship column value.
     *
     * @return string|int|float|bool|null A relationship column value.
     */
    public function getRelationColumn(
        Entity $entity,
        string $relationName,
        string $id,
        string $column
    ): string|int|float|bool|null;

    /**
     * Select related entities from DB.
     *
     * @return Collection<Entity>|Entity|null
     */
    public function selectRelated(Entity $entity, string $relationName, ?Select $select = null): Collection|Entity|null;

    /**
     * Get a number of related entities in DB.
     */
    public function countRelated(Entity $entity, string $relationName, ?Select $select = null): int;
}
