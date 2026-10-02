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

use Espo\ORM\Defs\Params\FieldParam;
use RuntimeException;

/**
 * Field definitions.
 */
class FieldDefs
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
        $type = $this->data[FieldParam::TYPE] ?? null;

        if ($type === null) {
            throw new RuntimeException("Field '$this->name' has no type.");
        }

        return $type;
    }

    /**
     * Whether is not-storable.
     */
    public function isNotStorable(): bool
    {
        return $this->data[FieldParam::NOT_STORABLE] ?? false;
    }

    /**
     * Get a parameter value by a name.
     */
    public function getParam(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Has a parameter value.
     */
    public function hasParam(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}
