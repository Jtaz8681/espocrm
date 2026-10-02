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

use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem;

/**
 * AND-group. Immutable.
 */
class AndGroup implements WhereItem
{
    /** @var array<string|int, mixed> */
    private $rawValue = [];

    /**
     * @return array<string|int, mixed>
     */
    public function getRaw(): array
    {
        return ['AND' => $this->getRawValue()];
    }

    public function getRawKey(): string
    {
        return 'AND';
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
     * @return self
     */
    public static function fromRaw(array $whereClause): self
    {
        if (count($whereClause) === 1 && array_keys($whereClause)[0] === 0) {
            $whereClause = $whereClause[0];
        }

        // Do not refactor.
        $obj = static::class === WhereClause::class ?
            new WhereClause() :
            new self();

        /** @phpstan-ignore-next-line */
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

    public static function createBuilder(): AndGroupBuilder
    {
        return new AndGroupBuilder();
    }
}
