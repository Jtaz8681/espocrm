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

use Espo\ORM\Query\Part\Expression;
use RuntimeException;

class UpdateBuilder implements Builder
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
     * Build a UPDATE query.
     */
    public function build(): Update
    {
        return Update::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(Update $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * For what entity type to build a query.
     */
    public function in(string $entityType): self
    {
        if (isset($this->params['from'])) {
            throw new RuntimeException("Method 'in' can be called only once.");
        }

        $this->params['from'] = $entityType;

        return $this;
    }

    /**
     * Values to set. Column => Value map.
     *
     * @param array<string, scalar|Expression|null> $set
     */
    public function set(array $set): self
    {
        $modified = [];

        foreach ($set as $key => $value) {
            if (!$value instanceof Expression) {
                $modified[$key] = $value;

                continue;
            }

            $newKey = rtrim($key, ':')  . ':';

            $modified[$newKey] = $value->getValue();
        }

        $this->params['set'] = $modified;

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
