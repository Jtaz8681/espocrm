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

namespace Espo\Tools\WorkingTime\Util;

use Espo\Entities\WorkingTimeCalendar;
use Espo\Entities\WorkingTimeRange;
use Espo\Tools\WorkingTime\Calendar\WorkingDate;

class CalendarUtil
{
    private WorkingTimeCalendar $workingTimeCalendar;

    public function __construct(WorkingTimeCalendar $workingTimeCalendar)
    {
        $this->workingTimeCalendar = $workingTimeCalendar;
    }

    /**
     * @param WorkingTimeRange $range
     * @return WorkingDate[]
     */
    public function rangeToDates(WorkingTimeRange $range): array
    {
        $isWorking = $range->getType() === WorkingTimeRange::TYPE_WORKING;

        $list = [];

        $pointer = $range->getDateStart();
        $endPlusOne = $range->getDateEnd()->modify('+1 day');

        $defaultTimeRanges = $this->workingTimeCalendar->getTimeRanges();

        while ($pointer->isLessThan($endPlusOne)) {
            $timeRanges = $isWorking ? $range->getTimeRanges() : [];

            if ($isWorking && $timeRanges === null) {
                $timeRanges = $defaultTimeRanges;
            }

            $list[] = new WorkingDate($pointer, $timeRanges ?? []);

            $pointer = $pointer->modify('+1 day');
        }

        return $list;
    }
}
