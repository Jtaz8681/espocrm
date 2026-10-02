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

namespace Espo\ORM\Repository;

use Espo\ORM\Entity;

/**
 * An access point for record fetching and storing.
 *
 * @template TEntity of Entity
 */
interface Repository
{
    /**
     * Get a new entity.
     *
     * @return TEntity
     */
    public function getNew(): Entity;

    /**
     * Fetch an entity by ID.
     *
     * @return ?TEntity
     */
    public function getById(string $id): ?Entity;

    /**
     * Store an entity.
     *
     * @param TEntity $entity
     * @param array<string, mixed> $options
     */
    public function save(Entity $entity, array $options = []): void;

    /**
     * Remove an entity.
     *
     * @param TEntity $entity
     * @param array<string, mixed> $options
     */
    public function remove(Entity $entity, array $options = []): void;
}
