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
 * OR-group. Immutable.
 */
class OrGroup implements WhereItem
{

    /** @var array<string|int, mixed> */
    private $rawValue = [];

    public function __construct()
    {
    }

    public function getRaw(): array
    {
        return ['OR' => $this->rawValue];
    }

    public function getRawKey(): string
    {
        return 'OR';
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getRawValue(): array
    {
        return $this->rawValue;
    }

    /**
     * Get a number of items.
     */
    public function getItemCount(): int
    {
        return count($this->rawValue);
    }

    /**
     * @param array<string|int, mixed> $whereClause
     */
    public static function fromRaw(array $whereClause): self
    {
        $obj = new self();

        $obj->rawValue = $whereClause;

        return $obj;
    }

    public static function create(WhereItem ...$itemList): self
    {
        $builder = self::createBuilder();

        foreach ($itemList as $item) {
            $builder->add($item);
        }

        return $builder->build();
    }

    public static function createBuilder(): OrGroupBuilder
    {
        return new OrGroupBuilder();
    }
}
