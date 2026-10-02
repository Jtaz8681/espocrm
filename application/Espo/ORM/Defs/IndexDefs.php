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

use Espo\ORM\Defs\Params\IndexParam;

/**
 * Index definitions.
 */
class IndexDefs
{
    /** @var array<string, mixed> */
    private $data;
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
     * Get a key.
     */
    public function getKey(): string
    {
        return $this->data[IndexParam::KEY] ?? '';
    }

    /**
     * Whether is unique.
     */
    public function isUnique(): bool
    {
        // For bc.
        if (($this->data['unique'] ?? false)) {
            return true;
        }

        $type = $this->data[IndexParam::TYPE] ?? null;

        return $type === 'unique';
    }

    /**
     * Get a column list.
     *
     * @return string[]
     */
    public function getColumnList(): array
    {
        return $this->data[IndexParam::COLUMNS] ?? [];
    }

    /**
     * Get a flag list.
     *
     * @return string[]
     */
    public function getFlagList(): array
    {
        return $this->data[IndexParam::FLAGS] ?? [];
    }
}
