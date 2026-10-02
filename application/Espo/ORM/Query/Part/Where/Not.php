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

namespace Espo\ORM\Query\Part\Where;

use Espo\ORM\Query\Part\WhereItem;

/**
 * A NOT-operator. Immutable.
 */
class Not implements WhereItem
{
    /** @var array<string|int, mixed> */
    private $rawValue = [];

    public function getRaw(): array
    {
        return ['NOT' => $this->getRawValue()];
    }

    public function getRawKey(): string
    {
        return 'NOT';
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getRawValue(): array
    {
        return $this->rawValue;
    }

    /**
     * @param array<string|int, mixed> $whereClause
     */
    public static function fromRaw(array $whereClause): self
    {
        if (count($whereClause) === 1 && array_keys($whereClause)[0] === 0) {
            $whereClause = $whereClause[0];
        }

        $obj = new self();

        $obj->rawValue = $whereClause;

        return $obj;
    }

    public static function create(WhereItem $item): self
    {
        return self::fromRaw($item->getRaw());
    }
}
