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

namespace Espo\ORM\Query\Part;

use InvalidArgumentException;
use Iterator;

/**
 * A list of order items.
 *
 * Immutable.
 *
 * @implements Iterator<Order>
 */
class OrderList implements Iterator
{
    private int $position = 0;
    /** @var Order[] */
    private array $list;

    /**
     * @param Order[] $list
     */
    private function __construct(array $list)
    {
        foreach ($list as $item) {
            if (!$item instanceof Order) {
                throw new InvalidArgumentException();
            }
        }

        $this->list = $list;
    }

    /**
     * Create an instance.
     *
     * @param Order[] $list
     */
    public static function create(array $list): self
    {
        return new self($list);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): Order
    {
        return $this->list[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->list[$this->position]);
    }
}
