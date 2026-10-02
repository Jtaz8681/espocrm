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
use LogicException;
use RuntimeException as RuntimeExceptionAlias;

class EmptyRelations implements Relations
{
    /** @var array<string, Entity|null> */
    private array $setData = [];

    public function __construct() {}

    public function resetAll(): void
    {
        $this->setData = [];
    }

    public function reset(string $relation): void
    {
        unset($this->setData[$relation]);
    }

    /**
     * @param Entity|null $related
     */
    public function set(string $relation, Entity|null $related): void
    {
        $this->setData[$relation] = $related;
    }

    public function isSet(string $relation): bool
    {
        return array_key_exists($relation, $this->setData);
    }

    /**
     * @return Entity|null
     */
    public function getSet(string $relation): Entity|null
    {
        if (!array_key_exists($relation, $this->setData)) {
            throw new RuntimeExceptionAlias("Relation '$relation' is not set.");
        }

        return $this->setData[$relation];
    }

    public function getOne(string $relation): ?Entity
    {
        $entity = $this->setData[$relation] ?? null;

        if ($entity instanceof EntityCollection) {
            throw new LogicException("Not an entity.");
        }

        return $entity;
    }

    /***
     * @return EntityCollection<Entity>
     */
    public function getMany(string $relation): EntityCollection
    {
        $collection = $this->setData[$relation] ?? new EntityCollection();

        if (!$collection instanceof EntityCollection) {
            throw new LogicException("Not a collection.");
        }

        /** @var EntityCollection<Entity> */
        return $collection;
    }
}
