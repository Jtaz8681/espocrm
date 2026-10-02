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

namespace Espo\Core\FieldProcessing\Loader;

/**
 * Immutable.
 */
class Params
{
    /** @var ?string[] */
    private ?array $select = null;

    public function __construct() {}

    public function hasInSelect(string $field): bool
    {
        return $this->hasSelect() && in_array($field, $this->select ?? []);
    }

    public function hasSelect(): bool
    {
        return $this->select !== null;
    }

    /**
     * @return ?string[]
     */
    public function getSelect(): ?array
    {
        return $this->select;
    }

    /**
     * @param ?string[] $select
     */
    public function withSelect(?array $select): self
    {
        $obj = clone $this;
        $obj->select = $select;

        return $obj;
    }

    public static function create(): self
    {
        return new self();
    }
}
