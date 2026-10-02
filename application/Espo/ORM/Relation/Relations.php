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

namespace Espo\ORM\Relation;

use Espo\ORM\Entity;
use Espo\ORM\EntityCollection;

/**
 * @internal Not ready for production.
 */
interface Relations
{
    /**
     * Reset a specific relation.
     */
    public function reset(string $relation): void;

    /**
     * Reset all.
     */
    public function resetAll(): void;

    /**
     * @param Entity|null $related
     */
    public function set(string $relation, Entity|null $related): void;

    /**
     * Is a relation set (updated).
     */
    public function isSet(string $relation): bool;

    /**
     * Get set (updated) record or records.
     *
     * @return Entity|null
     */
    public function getSet(string $relation): Entity|null;

    /**
     * Get one related record. For has-one, belongs-to.
     */
    public function getOne(string $relation): ?Entity;

    /**
     * Get a collection of related records. For has-many, many-many, has-children.
     *
     * @return EntityCollection<Entity>
     */
    public function getMany(string $relation): EntityCollection;
}
