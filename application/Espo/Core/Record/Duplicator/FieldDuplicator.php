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

namespace Espo\Core\Record\Duplicator;

use Espo\ORM\Entity;

use stdClass;

/**
 * Duplicates attributes of a field. Some fields can require some processing
 * when an entity is being duplicated.
 *
 * @template TEntity of Entity = Entity
 */
interface FieldDuplicator
{
    /**
     * @param TEntity $entity
     */
    public function duplicate(Entity $entity, string $field): stdClass;
}
