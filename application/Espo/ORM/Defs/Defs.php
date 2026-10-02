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

namespace Espo\ORM\Defs;

use RuntimeException;

/**
 * Definitions.
 */
class Defs
{
    public function __construct(private DefsData $data)
    {}

    /**
     * Get an entity type list.
     *
     * @return string[]
     */
    public function getEntityTypeList(): array
    {
        return $this->data->getEntityTypeList();
    }

    /**
     * Get an entity definitions list.
     *
     * @return EntityDefs[]
     */
    public function getEntityList(): array
    {
        $list = [];

        foreach ($this->getEntityTypeList() as $name) {
            $list[] = $this->getEntity($name);
        }

        return $list;
    }

    /**
     * Has an entity type.
     */
    public function hasEntity(string $entityType): bool
    {
        return $this->data->hasEntity($entityType);
    }

    /**
     * Get entity definitions.
     */
    public function getEntity(string $entityType): EntityDefs
    {
        if (!$this->hasEntity($entityType)) {
            throw new RuntimeException("Entity type '{$entityType}' does not exist.");
        }

        return $this->data->getEntity($entityType);
    }

    /**
     * Try to get entity definitions, if an entity type does not exist, then return null.
     */
    public function tryGetEntity(string $entityType): ?EntityDefs
    {
        if (!$this->hasEntity($entityType)) {
            return null;
        }

        return $this->getEntity($entityType);
    }
}
