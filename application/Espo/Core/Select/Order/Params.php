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
 * Order parameters.
 *
 * Immutable.
 */
class Params
{
    private bool $forceDefault = false;
    private mixed $orderBy = null;
    /** @var SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null */
    private $order = null;
    private bool $applyPermissionCheck = false;

    private function __construct() {}

    /**
     * @param array{
     *     forceDefault?: bool,
     *     orderBy?: ?string,
     *     order?: SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null,
     *     applyPermissionCheck?: bool,
     * } $params
     */
    public static function fromAssoc(array $params): self
    {
        $object = new self();

        $object->forceDefault = $params['forceDefault'] ?? false;
        $object->orderBy = $params['orderBy'] ?? null;
        $object->order = $params['order'] ?? null;
        $object->applyPermissionCheck = $params['applyPermissionCheck'] ?? false;

        foreach ($params as $key => $value) {
            if (!property_exists($object, $key)) {
                throw new InvalidArgumentException("Unknown parameter '{$key}'.");
            }
        }

        if ($object->orderBy && !is_string($object->orderBy)) {
            throw new InvalidArgumentException("Bad orderBy.");
        }

        /** @var ?string $order */
        $order = $object->order;

        if (
            $order &&
            $order !== SearchParams::ORDER_ASC &&
            $order !== SearchParams::ORDER_DESC
        ) {
            throw new InvalidArgumentException("Bad order.");
        }

        return $object;
    }

    /**
     * Force default order.
     */
    public function forceDefault(): bool
    {
        return $this->forceDefault;
    }

    /**
     * An order-By field.
     */
    public function getOrderBy(): ?string
    {
        /** @var ?string */
        return $this->orderBy;
    }

    /**
     * An order direction.
     *
     * @return SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }

    /**
     * Apply permission check.
     */
    public function applyPermissionCheck(): bool
    {
        return $this->applyPermissionCheck;
    }
}
