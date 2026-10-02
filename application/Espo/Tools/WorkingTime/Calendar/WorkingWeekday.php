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

namespace Espo\Tools\WorkingTime\Calendar;

class WorkingWeekday implements HavingRanges
{
    /**
     * @var int<0,6>
     */
    private int $weekday;

    /**
     * @var TimeRange[]
     */
    private array $ranges;

    /**
     * @param int<0,6> $weekday
     * @param TimeRange[] $ranges
     */
    public function __construct(int $weekday, array $ranges)
    {
        $this->weekday = $weekday;
        $this->ranges = $ranges;
    }

    /**
     * @return int<0,6>
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * @return TimeRange[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }
}
