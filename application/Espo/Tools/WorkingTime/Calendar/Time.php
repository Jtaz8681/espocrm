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

class Time
{
    /**
     * @var int<0,23>
     */
    private int $hour;

    /**
     * @var int<0,59>
     */
    private int $minute;

    /**
     * @param int<0,23> $hour
     * @param int<0,59> $minute
     */
    public function __construct(int $hour, int $minute)
    {
        $this->hour = $hour;
        $this->minute = $minute;
    }

    /**
     * @return int<0,23>
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * @return int<0,59>
     */
    public function getMinute(): int
    {
        return $this->minute;
    }
}
