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

namespace Espo\ORM\Defs;

use Espo\ORM\Defs\Params\AttributeParam;

/**
 * Attribute definitions.
 */
class AttributeDefs
{
    /** @var array<string, mixed> */
    private array $data;
    private string $name;

    private function __construct()
    {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromRaw(array $raw, string $name): self
    {
        $obj = new self();
        $obj->data = $raw;
        $obj->name = $name;

        return $obj;
    }

    /**
     * Get a name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a type.
     */
    public function getType(): string
    {
        return $this->data[AttributeParam::TYPE];
    }

    /**
     * Get a length.
     */
    public function getLength(): ?int
    {
        return $this->data[AttributeParam::LEN] ?? null;
    }

    /**
     * Whether is not-storable. Not-storable attributes are not stored in DB.
     */
    public function isNotStorable(): bool
    {
        return $this->data[AttributeParam::NOT_STORABLE] ?? false;
    }

    /**
     * Whether is auto-increment.
     */
    public function isAutoincrement(): bool
    {
        return $this->data[AttributeParam::AUTOINCREMENT] ?? false;
    }

    /**
     * Whether a parameter is set.
     */
    public function hasParam(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Get a parameter value by a name.
     *
     * @return mixed
     */
    public function getParam(string $name)
    {
        return $this->data[$name] ?? null;
    }
}
