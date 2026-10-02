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

namespace Espo\Core\Record;

/**
 * Immutable.
 */
class UpdateParams
{
    private bool $skipDuplicateCheck = false;
    private ?int $versionNumber = null;

    public function __construct() {}

    public function withSkipDuplicateCheck(bool $skipDuplicateCheck = true): self
    {
        $obj = clone $this;
        $obj->skipDuplicateCheck = $skipDuplicateCheck;

        return $obj;
    }

    public function withVersionNumber(?int $versionNumber): self
    {
        $obj = clone $this;
        $obj->versionNumber = $versionNumber;

        return $obj;
    }

    public function skipDuplicateCheck(): bool
    {
        return $this->skipDuplicateCheck;
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public static function create(): self
    {
        return new self();
    }
}
