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

use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Entities\WorkingTimeCalendar;
use Espo\Entities\WorkingTimeRange;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\Tools\WorkingTime\Calendar\WorkingWeekday;
use Espo\Tools\WorkingTime\Calendar\WorkingDate;
use Espo\Core\Field\Date;
use Espo\Tools\WorkingTime\Util\CalendarUtil;

use DateTimeZone;
use Exception;
use RuntimeException;

/**
 * @since 8.4.0
 */
class SpecificCalendar implements Calendar
{
    private ?CalendarUtil $util = null;

    /** @var ?array{WorkingDate[], WorkingDate[]} */
    private ?array $cache = null;
    private ?string $cacheKey = null;
    private DateTimeZone $timezone;

    public function __construct(
        private EntityManager $entityManager,
        private WorkingTimeCalendar $workingTimeCalendar,
        ApplicationConfig $applicationConfig,
    ) {
        try {
            $this->timezone = new DateTimeZone($applicationConfig->getTimeZone());
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $this->util = new CalendarUtil($this->workingTimeCalendar);

        $this->timezone = $this->workingTimeCalendar->getTimeZone() ?? $this->timezone;
    }

    /** @noinspection PhpUnused */
    public function isAvailable(): bool
    {
        return true;
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * @return WorkingWeekday[]
     */
    public function getWorkingWeekdays(): array
    {
        return $this->workingTimeCalendar->getWorkingWeekdays();
    }

    /**
     * @return WorkingDate[]
     */
    public function getNonWorkingDates(Date $from, Date $to): array
    {
        return $this->getDates($from, $to)[0];
    }

    /**
     * @return WorkingDate[]
     */
    public function getWorkingDates(Date $from, Date $to): array
    {
        return $this->getDates($from, $to)[1];
    }

    /**
     * @return array{WorkingDate[], WorkingDate[]}
     */
    private function getDates(Date $from, Date $to): array
    {
        $cacheKey = $from->toString() . '-' . $to->toString();

        if ($this->cacheKey === $cacheKey) {
            assert($this->cache !== null);

            return $this->cache;
        }

        $notWorkingList = [];
        $workingList = [];

        $list = $this->fetchRanges($from, $to);

        foreach ($list as $range) {
            $dates = $this->rangeToDates($range);

            if ($range->getType() === WorkingTimeRange::TYPE_NON_WORKING) {
                $notWorkingList = array_merge($notWorkingList, $dates);

                continue;
            }

            $workingList = array_merge($workingList, $dates);
        }

        $this->cacheKey = $cacheKey;
        $this->cache = [$notWorkingList, $workingList];

        return $this->cache;
    }

    /**
     * @param WorkingTimeRange $range
     * @return WorkingDate[]
     */
    private function rangeToDates(WorkingTimeRange $range): array
    {
        if (!$this->util) {
            return [];
        }

        return $this->util->rangeToDates($range);
    }

    /**
     * @return WorkingTimeRange[]
     */
    private function fetchRanges(Date $from, Date $to): array
    {
        $list = [];

        $collection = $this->entityManager
            ->getRDBRepositoryByClass(WorkingTimeRange::class)
            ->leftJoin('calendars')
            ->where(
                Condition::equal(
                    Expression::column('calendars.id'),
                    $this->workingTimeCalendar->getId()
                )
            )
            ->where(
                OrGroup::create(
                    Condition::greaterOrEqual(
                        Expression::column('dateEnd'),
                        $from->toString()
                    ),
                    Condition::lessOrEqual(
                        Expression::column('dateStart'),
                        $to->toString()
                    ),
                )
            )
            ->find();

        foreach ($collection as $entity) {
            $list[] = $entity;
        }

        return $list;
    }
}
