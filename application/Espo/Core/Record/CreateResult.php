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
 * @template TEntity of Entity = Entity
 * @since 10.0.0
 */
class CreateResult
{
    /**
     * @param TEntity $entity
     */
    public function __construct(
        private Entity $entity,
    ) {}

    public function getValueMap(): stdClass
    {
        return $this->entity->getValueMap();
    }

    /**
     * @return TEntity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @deprecated Since v10.0.0. For bc. Use entity->get(...).
     */
    public function getId(): string
    {
        return $this->entity->getId();
    }

    /**
     * @deprecated Since v10.0.0. For bc. Use entity->get(...).
     */
    public function get(string $name): mixed
    {
        return $this->entity->get($name);
    }
}
