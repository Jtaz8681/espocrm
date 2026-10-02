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

use Espo\Core\Field\Date;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\ORM\Entity;
use Espo\Tools\WorkingTime\Calendar\Time;
use Espo\Tools\WorkingTime\Calendar\TimeRange;
use RuntimeException;

class WorkingTimeRange extends Entity
{
    public const ENTITY_TYPE = 'WorkingTimeRange';

    public const TYPE_NON_WORKING = 'Non-working';
    public const TYPE_WORKING = 'Working';

    /**
     * @return (self::TYPE_NON_WORKING|self::TYPE_WORKING)
     */
    public function getType(): string
    {
        $type = $this->get('type');

        if (!$type) {
            throw new RuntimeException();
        }

        return $type;
    }

    public function getDateStart(): Date
    {
        /** @var ?Date $value */
        $value = $this->getValueObject('dateStart');

        if (!$value) {
            throw new RuntimeException();
        }

        return $value;
    }

    public function getDateEnd(): Date
    {
        /** @var ?Date $value */
        $value = $this->getValueObject('dateEnd');

        if (!$value) {
            throw new RuntimeException();
        }

        return $value;
    }

    /**
     * @return ?TimeRange[]
     */
    public function getTimeRanges(): ?array
    {
        $ranges = self::convertRanges($this->get('timeRanges') ?? []);

        if ($ranges === []) {
            return null;
        }

        return $ranges;
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

    public function getUsers(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('users');
    }

    /**
     * @since 9.3.1
     */
    public function getCalendars(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('calendars');
    }
}
