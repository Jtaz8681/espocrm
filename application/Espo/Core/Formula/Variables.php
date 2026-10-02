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

namespace Espo\Core\Formula;

use Espo\Core\Formula\Exceptions\Error;
use stdClass;

class Variables
{
    public function __construct(
        private stdClass $variables
    ) {}

    public function has(string $name): bool
    {
        return property_exists($this->variables, $name);
    }

    /**
     * @throws Error
     */
    public function get(string $name): mixed
    {
        if (!property_exists($this->variables, $name)) {
            throw new Error("Variable $name is not defined.");
        }

        return $this->variables->$name;
    }

    public function tryGet(string $name): mixed
    {
        return $this->variables->$name ?? null;
    }

    public function set(string $name, mixed $value): void
    {
        $this->variables->$name = $value;
    }
}
