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

namespace Espo\Core\Formula\Parser\Statement;

class StatementRef
{
    private bool $endedWithSemicolon = false;

    public function __construct(private int $start, private ?int $end = null)
    {}

    public function setEnd(int $end, bool $endedWithSemicolon = false): void
    {
        $this->end = $end;
        $this->endedWithSemicolon = $endedWithSemicolon;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function isReady(): bool
    {
        return $this->end !== null;
    }

    public function isEndedWithSemicolon(): bool
    {
        return $this->endedWithSemicolon;
    }
}
