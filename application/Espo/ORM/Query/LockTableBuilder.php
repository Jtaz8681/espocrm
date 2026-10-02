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

class LockTableBuilder implements Builder
{
    use BaseBuilderTrait;

    /**
     * Create an instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Build a LOCK TABLE query.
     */
    public function build(): LockTable
    {
        return LockTable::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(LockTable $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * What entity type to lock.
     */
    public function table(string $entityType): self
    {
        $this->params['table'] = $entityType;

        return $this;
    }

    /**
     * In SHARE mode.
     */
    public function inShareMode(): self
    {
        $this->params['mode'] = LockTable::MODE_SHARE;

        return $this;
    }

    /**
     * In EXCLUSIVE mode.
     */
    public function inExclusiveMode(): self
    {
        $this->params['mode'] = LockTable::MODE_EXCLUSIVE;

        return $this;
    }
}
