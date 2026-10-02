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

namespace Espo\Core\Mail\Account\Hook;

class BeforeFetchResult
{
    private bool $toSkip = false;
    /** @var array<string, mixed> */
    private array $data = [];

    public static function create(): self
    {
        return new self();
    }

    public function withToSkip(bool $toSkip = true): self
    {
        $obj = clone $this;
        $obj->toSkip = $toSkip;

        return $obj;
    }

    public function with(string $name, mixed $value): self
    {
        $obj = clone $this;
        $obj->data[$name] = $value;

        return $obj;
    }

    public function toSkip(): bool
    {
        return $this->toSkip;
    }

    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}
