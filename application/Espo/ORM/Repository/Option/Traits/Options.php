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

namespace Espo\ORM\Repository\Option\Traits;

trait Options
{
    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    private function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Create from an associative array.
     *
     * @param array<string, mixed> $options
     */
    public static function fromAssoc(array $options): self
    {
        return new self($options);
    }

    /**
     * Get an option value. Returns `null` if not set.
     */
    public function get(string $option): mixed
    {
        return $this->options[$option] ?? null;
    }

    /**
     * Whether an option is set.
     */
    public function has(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * Clone with an option value.
     */
    public function with(string $option, mixed $value): self
    {
        $obj = clone $this;
        $obj->options[$option] = $value;

        return $obj;
    }

    /**
     * Clone with an option removed.
     */
    public function without(string $option): self
    {
        $obj = clone $this;
        unset($obj->options[$option]);

        return $obj;
    }

    /**
     * @return array<string, mixed>
     */
    public function toAssoc(): array
    {
        return $this->options;
    }
}
