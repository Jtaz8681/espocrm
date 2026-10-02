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

namespace Espo\ORM\Repository\Deprecation;

use Espo\ORM\Entity;

/**
 * @internal
 * @template TEntity of Entity
 */
trait RDBRepositoryDeprecationTrait
{
    /**
     * Get an entity. If ID is NULL, a new entity is returned.
     *
     * @deprecated Use `getById` and `getNew`.
     * @todo Remove in v11.0.
     */
    public function get(?string $id = null): ?Entity
    {
        if (is_null($id)) {
            return $this->getNew();
        }

        return $this->getById($id);
    }
}
