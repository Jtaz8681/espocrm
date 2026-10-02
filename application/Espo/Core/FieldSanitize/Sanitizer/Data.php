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

namespace Espo\Core\FieldSanitize\Sanitizer;

use stdClass;

/**
 * Input data. No 'clear' method, as unsetting is not supposed to happen in sanitization.
 */
class Data
{
    public function __construct(private stdClass $data)
    {}

    /**
     * Get a value.
     */
    public function get(string $attribute): mixed
    {
        return $this->data->$attribute ?? null;
    }


    /**
     * Whether a value is set.
     */
    public function has(string $attribute): bool
    {
        return property_exists($this->data, $attribute);
    }

    /**
     * Update a value.
     */
    public function set(string $attribute, mixed $value): self
    {
        $this->data->$attribute = $value;

        return $this;
    }

    /**
     * Unset an attribute.
     */
    public function clear(string $attribute): self
    {
        unset($this->data->$attribute);

        return $this;
    }
}
