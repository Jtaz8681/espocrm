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

class InsertBuilder implements Builder
{
    use BaseBuilderTrait;

    /**
     * @var array<string, mixed>
     */
    protected $params = [];

    /**
     * Create an instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Build a INSERT query.
     */
    public function build(): Insert
    {
        return Insert::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(Insert $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * Into what entity type to insert.
     */
    public function into(string $entityType): self
    {
        $this->params['into'] = $entityType;

        return $this;
    }

    /**
     * What columns to set with values. A list of columns.
     *
     * @param string[] $columns
     */
    public function columns(array $columns): self
    {
        $this->params['columns'] = $columns;

        return $this;
    }

    /**
     * What values to insert. A key-value map or a list of key-value maps.
     *
     * @param array<string, ?scalar>|array<string, ?scalar>[] $values
     */
    public function values(array $values): self
    {
        $this->params['values'] = $values;

        return $this;
    }

    /**
     * Values to set on duplicate key. A key-value map.
     *
     * @param array<string, ?scalar> $updateSet
     */
    public function updateSet(array $updateSet): self
    {
        $this->params['updateSet'] = $updateSet;

        return $this;
    }

    /**
     * For a mass insert by a select sub-query.
     */
    public function valuesQuery(SelectingQuery $query): self
    {
        $this->params['valuesQuery'] = $query;

        return $this;
    }
}
