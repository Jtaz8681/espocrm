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

namespace Espo\Core\MassAction;

use RuntimeException;

/**
 * Immutable.
 */
class Result
{
    private ?int $count = null;
    /** @var ?string[] */
    private $ids = null;

    /**
     * @param ?string[] $ids
     */
    public function __construct(?int $count, ?array $ids = null)
    {
        $this->count = $count;
        $this->ids = $ids;
    }

    public function hasIds(): bool
    {
        return $this->ids !== null;
    }

    public function hasCount(): bool
    {
        return $this->count !== null;
    }

    /**
     * @return string[]
     */
    public function getIds(): array
    {
        if (!$this->hasIds()) {
            throw new RuntimeException("No IDs.");
        }

        /** @var string[] */
        return $this->ids;
    }

    public function getCount(): int
    {
        if (!$this->hasCount()) {
            throw new RuntimeException("No count.");
        }

        /** @var int */
        return $this->count;
    }

    public function withNoIds(): self
    {
        return new self($this->count);
    }

    /**
     * @deprecated
     * @param array{
     *   count?: ?int,
     *   ids?: ?string[],
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['count'] ?? null,
            $data['ids'] ?? null
        );
    }
}
