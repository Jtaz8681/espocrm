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

namespace Espo\Modules\Crm\Tools\Lead\Convert;

use Espo\Core\Utils\ObjectUtil;
use stdClass;
use UnexpectedValueException;

/**
 * Raw attribute values of multiple records.
 *
 * Immutable.
 */
class Values
{
    /** @var array<string, stdClass> */
    private array $data = [];

    public static function create(): self
    {
        return new self();
    }

    public function has(string $entityType): bool
    {
        return array_key_exists($entityType, $this->data);
    }

    public function get(string $entityType): stdClass
    {
        $data = $this->data[$entityType] ?? null;

        if ($data === null) {
            throw new UnexpectedValueException();
        }

        return ObjectUtil::clone($data);
    }

    public function with(string $entityType, stdClass $data): self
    {
        $obj = clone $this;
        $obj->data[$entityType] = ObjectUtil::clone($data);

        return $obj;
    }

    public function getRaw(): stdClass
    {
        $data = (object) [];

        foreach ($this->data as $entityType => $item) {
            $data->$entityType = ObjectUtil::clone($item);
        }

        return $data;
    }
}
