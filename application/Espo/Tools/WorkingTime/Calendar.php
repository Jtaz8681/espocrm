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

namespace Espo\Tools\WorkingTime;

use Espo\Tools\WorkingTime\Calendar\WorkingWeekday;
use Espo\Tools\WorkingTime\Calendar\WorkingDate;

use Espo\Core\Field\Date;

use DateTimeZone;

interface Calendar
{
    /**
     * Time-zone.
     */
    public function getTimezone(): DateTimeZone;

    /**
     * Working weekdays.
     *
     * @return WorkingWeekday[]
     */
    public function getWorkingWeekdays(): array;

    /**
     * Non-working dates.
     *
     * @return WorkingDate[]
     */
    public function getNonWorkingDates(Date $from, Date $to): array;

    /**
     * Working dates (exceptions).
     *
     * @return WorkingDate[]
     */
    public function getWorkingDates(Date $from, Date $to): array;
}
