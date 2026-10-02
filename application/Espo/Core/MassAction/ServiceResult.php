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

namespace Espo\Core\MassAction;

/**
 * Immutable.
 */
class ServiceResult
{
    private ?Result $result = null;
    private ?string $id = null;

    private function __construct() {}

    public function hasResult(): bool
    {
        return $this->result !== null;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getResult(): ?Result
    {
        return $this->result;
    }

    public static function createWithId(string $id): self
    {
        $obj = new self;
        $obj->id = $id;

        return $obj;
    }

    public static function createWithResult(Result $result): self
    {
        $obj = new self;
        $obj->result = $result;

        return $obj;
    }
}
