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

namespace Espo\ORM\Query;

use RuntimeException;

class DeleteBuilder implements Builder
{
    use SelectingBuilderTrait;

    /**
     * Create an instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Build a DELETE query.
     */
    public function build(): Delete
    {
        return Delete::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(Delete $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * Set FROM parameter. For what entity type to build a query.
     */
    public function from(string $entityType, ?string $alias = null): self
    {
        if (isset($this->params['from'])) {
            throw new RuntimeException("Method 'from' can be called only once.");
        }

        $this->params['from'] = $entityType;
        $this->params['fromAlias'] = $alias;

        return $this;
    }

    /**
     * Apply LIMIT.
     */
    public function limit(?int $limit = null): self
    {
        $this->params['limit'] = $limit;

        return $this;
    }
}
