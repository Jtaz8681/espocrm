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

namespace tests\unit\Espo\Tools\WorkingTime;

use Espo\Tools\WorkingTime\Calendar;
use Espo\Tools\WorkingTime\Calendar\Time;
use Espo\Tools\WorkingTime\Calendar\TimeRange;
use Espo\Tools\WorkingTime\Calendar\WorkingDate;
use Espo\Tools\WorkingTime\Calendar\WorkingWeekday;
use Espo\Tools\WorkingTime\Extractor;

use Espo\Core\Field\DateTime;
use Espo\Core\Field\Date;

use DateTimeZone;

class ExtractorTest extends \PHPUnit\Framework\TestCase
{
    protected Calendar $calendar;

    protected function setUp(): void
    {
        $this->calendar = $this->createMock(Calendar::class);
    }

    private function initCalendar1(): void
    {
        $this->calendar
            ->expects($this->any())
            ->method('getTimeZone')
            ->willReturn(new DateTimeZone('UTC'));

        $ranges = [
            new TimeRange(new Time(9, 0), new Time(13, 0)),
            new TimeRange(new Time(14, 0), new Time(17, 0)),
        ];

        $this->calendar
            ->expects($this->any())
            ->method('getWorkingDates')
            ->willReturn([
                new WorkingDate(
                    new Date('2022-01-05'),
                    [
                        new TimeRange(new Time(10, 30), new Time(15, 30)),
                    ]
                ),
                new WorkingDate(
                    new Date('2022-01-09'),
                    [
                        new TimeRange(new Time(10, 30), new Time(15, 30)),
                    ]
                )
            ]);

        $this->calendar
            ->expects($this->any())
            ->method('getNonWorkingDates')
            ->willReturn([
                new WorkingDate(new Date('2022-01-06')),
                new WorkingDate(new Date('2022-01-10')),
            ]);

        $this->calendar
            ->expects($this->any())
            ->method('getWorkingWeekdays')
            ->willReturn([
                new WorkingWeekday(1, $ranges),
                new WorkingWeekday(2, $ranges),
                new WorkingWeekday(3, $ranges),
                new WorkingWeekday(4, $ranges),
                new WorkingWeekday(5, $ranges),
            ]);
    }

    public function testExtract1(): void
    {
        $this->initCalendar1();
        $extractor = new Extractor();

        $list = $extractor->extract(
            $this->calendar,
            DateTime::fromString('2022-01-02 00:00:00'), // sun
            DateTime::fromString('2022-01-17 00:00:00') // sun
        );

        // mon
        $this->assertEquals('2022-01-03 09:00:00', $list[0][0]->getString());
        $this->assertEquals('2022-01-03 13:00:00', $list[0][1]->getString());

        $this->assertEquals('2022-01-03 14:00:00', $list[1][0]->getString());
        $this->assertEquals('2022-01-03 17:00:00', $list[1][1]->getString());

        // tue
        $this->assertEquals('2022-01-04 09:00:00', $list[2][0]->getString());
        $this->assertEquals('2022-01-04 13:00:00', $list[2][1]->getString());

        $this->assertEquals('2022-01-04 14:00:00', $list[3][0]->getString());
        $this->assertEquals('2022-01-04 17:00:00', $list[3][1]->getString());

        // wed working-date
        $this->assertEquals('2022-01-05 10:30:00', $list[4][0]->getString());
        $this->assertEquals('2022-01-05 15:30:00', $list[4][1]->getString());

        // thu non-working-date

        // fri
        $this->assertEquals('2022-01-07 09:00:00', $list[5][0]->getString());
        $this->assertEquals('2022-01-07 13:00:00', $list[5][1]->getString());

        $this->assertEquals('2022-01-07 14:00:00', $list[6][0]->getString());
        $this->assertEquals('2022-01-07 17:00:00', $list[6][1]->getString());

        // sat

        // sun working-day
        $this->assertEquals('2022-01-09 10:30:00', $list[7][0]->getString());
        $this->assertEquals('2022-01-09 15:30:00', $list[7][1]->getString());

        // mon non-working-day

        // tue
        $this->assertEquals('2022-01-11 09:00:00', $list[8][0]->getString());
        $this->assertEquals('2022-01-11 13:00:00', $list[8][1]->getString());

        $this->assertEquals('2022-01-11 14:00:00', $list[9][0]->getString());
        $this->assertEquals('2022-01-11 17:00:00', $list[9][1]->getString());
    }

    public function testExtractInversion1(): void
    {
        $this->initCalendar1();
        $extractor = new Extractor();

        $listInversion = $extractor->extractInversion(
            $this->calendar,
            DateTime::fromString('2022-01-02 00:00:00'), // sun
            DateTime::fromString('2022-01-17 00:00:00') // sun
        );

        $this->assertEquals('2022-01-02 00:00:00', $listInversion[0][0]->getString());
        $this->assertEquals('2022-01-03 09:00:00', $listInversion[0][1]->getString());

        $this->assertEquals('2022-01-14 13:00:00', $listInversion[15][0]->getString());
        $this->assertEquals('2022-01-14 14:00:00', $listInversion[15][1]->getString());

        $this->assertEquals('2022-01-14 17:00:00', $listInversion[16][0]->getString());
        $this->assertEquals('2022-01-17 00:00:00', $listInversion[16][1]->getString());


        $listAllDayInversion = $extractor->extractAllDayInversion(
            $this->calendar,
            DateTime::fromString('2022-01-02 00:00:00'), // sun
            DateTime::fromString('2022-01-17 00:00:00') // sun
        );

        $this->assertEquals('2022-01-02 00:00:00', $listAllDayInversion[0][0]->getString());
        $this->assertEquals('2022-01-03 00:00:00', $listAllDayInversion[0][1]->getString());

        $this->assertEquals('2022-01-14 00:00:00', $listAllDayInversion[8][0]->getString());
        $this->assertEquals('2022-01-14 00:00:00', $listAllDayInversion[8][1]->getString());

        $this->assertEquals('2022-01-15 00:00:00', $listAllDayInversion[9][0]->getString());
        $this->assertEquals('2022-01-17 00:00:00', $listAllDayInversion[9][1]->getString());
    }

    public function testExtractAllDay1(): void
    {
        $this->initCalendar1();
        $extractor = new Extractor();

        $listAllDay = $extractor->extractAllDay(
            $this->calendar,
            DateTime::fromString('2022-01-02 00:00:00'), // sun
            DateTime::fromString('2022-01-17 00:00:00') // sun
        );

        // mon
        $this->assertEquals('2022-01-03 00:00:00', $listAllDay[0][0]->getString());
        $this->assertEquals('2022-01-04 00:00:00', $listAllDay[0][1]->getString());

        // tue
        $this->assertEquals('2022-01-04 00:00:00', $listAllDay[1][0]->getString());
        $this->assertEquals('2022-01-05 00:00:00', $listAllDay[1][1]->getString());

        // wed
        $this->assertEquals('2022-01-05 00:00:00', $listAllDay[2][0]->getString());
        $this->assertEquals('2022-01-06 00:00:00', $listAllDay[2][1]->getString());

        // thu non-working-date

        // fri
        $this->assertEquals('2022-01-07 00:00:00', $listAllDay[3][0]->getString());
        $this->assertEquals('2022-01-08 00:00:00', $listAllDay[3][1]->getString());

        // sat

        // sun working-day
        $this->assertEquals('2022-01-09 00:00:00', $listAllDay[4][0]->getString());
        $this->assertEquals('2022-01-10 00:00:00', $listAllDay[4][1]->getString());

        // mon non-working-day

        // tue
        $this->assertEquals('2022-01-11 00:00:00', $listAllDay[5][0]->getString());
        $this->assertEquals('2022-01-12 00:00:00', $listAllDay[5][1]->getString());
    }

    public function testExtract2(): void
    {
        $this->initCalendar1();
        $extractor = new Extractor();

        $list = $extractor->extract(
            $this->calendar,
            DateTime::fromString('2022-01-03 10:00:00'),
            DateTime::fromString('2022-01-14 15:00:00')
        );

        $this->assertEquals('2022-01-03 10:00:00', $list[0][0]->getString());
        $this->assertEquals('2022-01-14 15:00:00', $list[count($list) - 1][1]->getString());
    }

    public function testExtract3(): void
    {
        $this->initCalendar1();
        $extractor = new Extractor();

        $list = $extractor->extract(
            $this->calendar,
            DateTime::fromString('2022-01-03 16:00:00'),
            DateTime::fromString('2022-01-14 15:00:00')
        );

        $this->assertEquals('2022-01-03 16:00:00', $list[0][0]->getString());
        $this->assertEquals('2022-01-03 17:00:00', $list[0][1]->getString());

        $this->assertEquals('2022-01-14 15:00:00', $list[count($list) - 1][1]->getString());
    }
}
