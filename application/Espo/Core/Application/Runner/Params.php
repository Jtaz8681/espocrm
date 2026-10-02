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

namespace Espo\Core\Application\Runner;

/**
 * Parameters for an application runner.
 */
class Params
{
    /** @var array<string, mixed> */
    private $data = [];

    public function __construct() {}

    /**
     * Get a parameter value.
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Whether a parameter is set.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Clone with a parameter value.
     */
    public function with(string $name, mixed $value): self
    {
        $obj = clone $this;
        $obj->data[$name] = $value;

        return $obj;
    }

    /**
     * Create from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->data = $data;

        return $obj;
    }

    /**
     * Create an empty instance.
     */
    public static function create(): self
    {
        return new self();
    }
}
