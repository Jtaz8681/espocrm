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

namespace Espo\Entities;

use Espo\Core\ORM\Entity;
use Espo\Tools\WorkingTime\Calendar\WorkingWeekday;
use Espo\Tools\WorkingTime\Calendar\TimeRange;
use Espo\Tools\WorkingTime\Calendar\Time;

use DateTimeZone;

class WorkingTimeCalendar extends Entity
{
    public const ENTITY_TYPE = 'WorkingTimeCalendar';

    public function getTimeZone(): ?DateTimeZone
    {
        $string = $this->get('timeZone');

        if (!$string) {
            return null;
        }

        return new DateTimeZone($string);
    }

    /**
     * @return TimeRange[]
     */
    public function getTimeRanges(): array
    {
        return self::convertRanges($this->get('timeRanges'));
    }

    /**
     * @param int<0,6> $weekday
     */
    private function hasCustomWeekdayRanges(int $weekday): bool
    {
        $attribute = 'weekday' . $weekday . 'TimeRanges';

        return $this->get($attribute) !== null && $this->get($attribute) !== [];
    }

    /**
     * @param int<0,6> $weekday
     * @return TimeRange[]
     */
    private function getWeekdayTimeRanges(int $weekday): array
    {
        $attribute = 'weekday' . $weekday . 'TimeRanges';

        $raw = $this->hasCustomWeekdayRanges($weekday) ?
            $this->get($attribute) :
            $this->get('timeRanges');

        return self::convertRanges($raw);
    }

    /**
     * @return WorkingWeekday[]
     */
    public function getWorkingWeekdays(): array
    {
        $list = [];

        for ($i = 0; $i <= 6; $i++) {
            if (!$this->get('weekday' . $i)) {
                continue;
            }

            $list[] = new WorkingWeekday($i, $this->getWeekdayTimeRanges($i));
        }

        return $list;
    }

    /**
     * @param array{string, string}[] $ranges
     * @return TimeRange[]
     */
    private static function convertRanges(array $ranges): array
    {
        $list = [];

        foreach ($ranges as $range) {
            $list[] = new TimeRange(
                self::convertTime($range[0]),
                self::convertTime($range[1])
            );
        }

        return $list;
    }

    private static function convertTime(string $time): Time
    {
        /** @var int<0, 23> $h */
        $h = (int) explode(':', $time)[0];
        /** @var int<0, 59> $m */
        $m = (int) explode(':', $time)[1];

        return new Time($h, $m);
    }
}
