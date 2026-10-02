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

namespace Espo\Core\Action;

use Espo\Core\Utils\ObjectUtil;

use stdClass;

class Data
{
    private stdClass $data;

    private function __construct()
    {
        $this->data = (object) [];
    }

    public function getRaw(): stdClass
    {
        return ObjectUtil::clone($this->data);
    }

    /**
     * Get an item value.
     *
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->getRaw()->$name ?? null;
    }

    /**
     * Has an item.
     */
    public function has(string $name): bool
    {
        return property_exists($this->data, $name);
    }

    public static function fromRaw(stdClass $data): self
    {
        $obj = new self();

        $obj->data = $data;

        return $obj;
    }

    /**
     * Clone with an item value.
     *
     * @param mixed $value
     */
    public function with(string $name, $value): self
    {
        $obj = clone $this;

        $obj->data->$name = $value;

        return $obj;
    }

    public function __clone()
    {
        $this->data = ObjectUtil::clone($this->data);
    }
}
