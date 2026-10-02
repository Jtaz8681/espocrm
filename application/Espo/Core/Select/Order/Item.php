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

namespace Espo\Core\Select\Order;

use Espo\Core\Select\SearchParams;

use InvalidArgumentException;

/**
 * Immutable.
 */
class Item
{
    private string $orderBy;
    /** @var SearchParams::ORDER_ASC|SearchParams::ORDER_DESC */
    private string $order;

    /**
     * @param SearchParams::ORDER_ASC|SearchParams::ORDER_DESC $order
     */
    private function __construct(string $orderBy, string $order)
    {
        if (
            $order !== SearchParams::ORDER_ASC &&
            $order !== SearchParams::ORDER_DESC
        ) {
            throw new InvalidArgumentException("Bad order.");
        }

        $this->orderBy = $orderBy;
        $this->order = $order;
    }

    /**
     * @param SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null $order
     */
    public static function create(string $orderBy, ?string $order = null): self
    {
        if ($order === null) {
            $order = SearchParams::ORDER_ASC;
        }

        return new self($orderBy, $order);
    }

    /**
     * Get a field.
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * Get a direction.
     *
     * @return SearchParams::ORDER_ASC|SearchParams::ORDER_DESC
     */
    public function getOrder(): string
    {
        return $this->order;
    }
}
