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

namespace Espo\Core\Select\Where\Item\Data;

use Espo\Core\Select\Where\Item\Data;

class Date implements Data
{
    private ?string $timeZone = null;

    public static function create(): self
    {
        return new self();
    }

    public function withTimeZone(?string $timeZone): self
    {
        $obj = clone $this;
        $obj->timeZone = $timeZone;

        return $obj;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }
}
