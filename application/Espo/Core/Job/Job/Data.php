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

namespace Espo\Core\Job\Job;

use Espo\Core\Utils\ObjectUtil;

use TypeError;
use stdClass;

class Data
{
    private stdClass $data;
    private ?string $targetId = null;
    private ?string $targetType = null;

    public function __construct(?stdClass $data = null)
    {
        $this->data = $data ?? (object) [];
    }

    /**
     * Create an instance.
     *
     * @param stdClass|array<string, mixed>|null $data Raw data.
     * @return self
     */
    public static function create($data = null): self
    {
        /** @var mixed $data */

        if ($data !== null && !is_object($data) && !is_array($data)) {
            throw new TypeError();
        }

        if (is_array($data)) {
            $data = (object) $data;
        }

        /** @var ?stdClass $data */

        return new self($data);
    }

    public function getRaw(): stdClass
    {
        return ObjectUtil::clone($this->data);
    }

    /**
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->getRaw()->$name ?? null;
    }

    public function has(string $name): bool
    {
        return property_exists($this->data, $name);
    }

    public function getTargetId(): ?string
    {
        return $this->targetId;
    }

    public function getTargetType(): ?string
    {
        return $this->targetType;
    }

    public function withTargetId(?string $targetId): self
    {
        $obj = clone $this;
        $obj->targetId = $targetId;

        return $obj;
    }

    public function withTargetType(?string $targetType): self
    {
        $obj = clone $this;
        $obj->targetType = $targetType;

        return $obj;
    }
}
