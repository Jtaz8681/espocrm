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

class OrGroupBuilder
{
    /** @var array<string|int, mixed> */
    private array $raw = [];

    public function build(): OrGroup
    {
        return OrGroup::fromRaw($this->raw);
    }

    public function add(WhereItem $item): self
    {
        $key = $item->getRawKey();
        $value = $item->getRawValue();

        if ($item instanceof AndGroup) {
            $this->raw = self::normalizeRaw($this->raw);

            $this->raw[] = $value;

            return $this;
        }

        if (count($this->raw) === 0) {
            $this->raw[$key] = $value;

            return $this;
        }

        $this->raw = self::normalizeRaw($this->raw);

        $this->raw[] = [$key => $value];

        return $this;
    }

    /**
     * Merge with another OrGroup.
     */
    public function merge(OrGroup $orGroup): self
    {
        $this->raw = array_merge(
            self::normalizeRaw($this->raw),
            self::normalizeRaw($orGroup->getRawValue())
        );

        return $this;
    }

    /**
     * @param array<string|int, mixed> $raw
     * @return array<string|int, mixed>
     */
    private static function normalizeRaw(array $raw): array
    {
        if (count($raw) === 1 && array_keys($raw)[0] !== 0) {
            return [$raw];
        }

        return $raw;
    }
}
