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

use Espo\ORM\Query\Part\Order;

use InvalidArgumentException;

class UnionBuilder implements Builder
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
     * Build a UNION select query.
     */
    public function build(): Union
    {
        return Union::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(Union $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * Use UNION ALL.
     */
    public function all(): self
    {
        $this->params['all'] = true;

        return $this;
    }

    public function query(Select $query): self
    {
        $this->params['queries'] = $this->params['queries'] ?? [];
        $this->params['queries'][] = $query;

        return $this;
    }

    /**
     * Apply OFFSET and LIMIT.
     */
    public function limit(?int $offset = null, ?int $limit = null): self
    {
        $this->params['offset'] = $offset;
        $this->params['limit'] = $limit;

        return $this;
    }

    /**
     * Apply ORDER.
     *
     * @param string|array<array{string, (Order::ASC|Order::DESC)|bool}|array{string}> $orderBy A select alias.
     * @param (Order::ASC|Order::DESC)|bool $direction A direction. True for DESC.
     */
    public function order($orderBy, string|bool $direction = Order::ASC): self
    {
        if (is_bool($direction)) {
            $direction = $direction ? Order::DESC : Order::ASC;
        }

        if (!$orderBy) {
            throw new InvalidArgumentException();
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $item) {
                /** @var mixed[] $item */

                if (count($item) === 2) {
                    /** @var array{string, bool|(Order::ASC|Order::DESC)} $item */
                    $this->order($item[0], $item[1]);

                    continue;
                }

                if (count($item) === 1) {
                    /** @var array{string} $item */
                    $this->order($item[0]);

                    continue;
                }

                throw new InvalidArgumentException("Bad order.");
            }

            return $this;
        }

        /** @var object|scalar $orderBy */

        if (!is_string($orderBy) && !is_int($orderBy)) {
            throw new InvalidArgumentException("Bad order.");
        }

        $this->params['orderBy'] = $this->params['orderBy'] ?? [];
        $this->params['orderBy'][] = [$orderBy, $direction];

        return $this;
    }
}
