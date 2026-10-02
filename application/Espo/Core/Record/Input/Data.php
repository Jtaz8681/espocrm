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

namespace Espo\Core\Record\Input;

use stdClass;

class Data
{
    public function __construct(private stdClass $raw) {}

    /**
     * Get all attributes.
     *
     * @return string[]
     */
    public function getAttributeList(): array
    {
        return array_keys(get_object_vars($this->raw));
    }

    /**
     * Unset an attribute.
     *
     * @param string $name An attribute name.
     */
    public function clear(string $name): self
    {
        unset($this->raw->$name);

        return $this;
    }

    /**
     * Whether an attribute is set.
     *
     * @param string $name An attribute name.
     */
    public function has(string $name): bool
    {
        return property_exists($this->raw, $name);
    }

    /**
     * Get an attribute value.
     *
     * @param string $name An attribute name.
     */
    public function get(string $name): mixed
    {
        return $this->raw->$name ?? null;
    }

    /**
     * Set an attribute value.
     *
     * @param string $name An attribute name.
     * @param mixed $value A value
     */
    public function set(string $name, mixed $value): mixed
    {
        return $this->raw->$name = $value;
    }
}
