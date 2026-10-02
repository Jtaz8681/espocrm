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

use Espo\Core\Field\DateTime;
use Espo\Tools\WorkingTime\Calendar;
use Espo\Tools\WorkingTime\CalendarUtility;
use Espo\Tools\WorkingTime\Extractor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CalendarUtilityTest extends TestCase
{
    protected Calendar $calendar;
    protected Extractor $extractor;
    protected CalendarUtility $calendarUtility;

    protected function setUp(): void
    {
        $this->calendar = $this->createMock(Calendar::class);
        $this->extractor = $this->createMock(Extractor::class);
        $this->calendarUtility = new CalendarUtility($this->calendar, $this->extractor);
    }

    public function testHasWorkingDay1(): void
    {
        $time = DateTime::fromString('2023-01-01 01:01:01');

        $from = DateTime::fromString('2023-01-01 00:00:00');
        $to = DateTime::fromString('2023-01-01 00:00:00');

        $this->extractor
            ->expects($this->any())
            ->method('extractAllDay')
            ->with($this->calendar, $from, $to)
            ->willReturn([
                [
                    $from,
                    $to
                ]
            ]);

        $this->assertTrue($this->calendarUtility->isWorkingDay($time));
    }

    public function testHasWorkingTime1(): void
    {
        $from = DateTime::fromString('2023-01-01 00:00:00');
        $to = DateTime::fromString('2023-01-02 00:00:00');

        $this->extractor
            ->expects($this->any())
            ->method('extract')
            ->with($this->calendar, $from, $to)
            ->willReturn([
                [
                    DateTime::fromString('2023-01-01 05:00:00'),
                    DateTime::fromString('2023-01-01 06:00:00')
                ]
            ]);

        $this->assertTrue($this->calendarUtility->hasWorkingTime($from, $to));
    }

    public function testHasWorkingTime2(): void
    {
        $from = DateTime::fromString('2023-01-01 00:00:00');
        $to = DateTime::fromString('2023-01-02 00:00:00');

        $this->extractor
            ->expects($this->any())
            ->method('extract')
            ->with($this->calendar, $from, $to)
            ->willReturn([]);

        $this->assertFalse($this->calendarUtility->hasWorkingTime($from, $to));
    }

    public function testGetSummedWorkingHours1(): void
    {
        $from = DateTime::fromString('2023-01-01 00:00:00');
        $to = DateTime::fromString('2023-01-02 00:00:00');

        $this->extractor
            ->expects($this->any())
            ->method('extract')
            ->with($this->calendar, $from, $to)
            ->willReturn([
                [
                    DateTime::fromString('2023-01-01 05:00:00'),
                    DateTime::fromString('2023-01-01 06:00:00')
                ],
                [
                    DateTime::fromString('2023-01-01 07:00:00'),
                    DateTime::fromString('2023-01-01 08:00:00')
                ]
            ]);

        $this->assertEquals(2.0, $this->calendarUtility->getSummedWorkingHours($from, $to));
    }

    public function testGetWorkingDays1(): void
    {
        $from = DateTime::fromString('2023-01-01 00:00:00');
        $to = DateTime::fromString('2023-01-07 00:00:00');

        $this->extractor
            ->expects($this->any())
            ->method('extractAllDay')
            ->with($this->calendar, $from, $to)
            ->willReturn([
                [
                    DateTime::fromString('2023-01-01 00:00:00'),
                    DateTime::fromString('2023-01-02 00:00:00')
                ],
                [
                    DateTime::fromString('2023-01-04 00:00:00'),
                    DateTime::fromString('2023-01-05 00:00:00')
                ]
            ]);

        $this->assertEquals(2, $this->calendarUtility->getWorkingDays($from, $to));
    }

    public function testFindClosestWorkingTime1(): void
    {
        $point = DateTime::fromString('2023-01-01 00:00:00');

        $invokedCount = $this->exactly(2);

        $this->extractor
            ->expects($invokedCount)
            ->method('extract')
            ->willReturnCallback(function ($calendar, $a, $b) use ($invokedCount, $point) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals($this->calendar, $calendar);
                    $this->assertEquals($point, $a);
                    $this->assertEquals($point->modify('+10 days'), $b);

                    return [];
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals($this->calendar, $calendar);
                    $this->assertEquals($point->modify('+10 days'), $a);
                    $this->assertEquals($point->modify('+20 days'), $b);

                    return [
                        [
                            DateTime::fromString('2023-01-11 01:00:00'),
                            DateTime::fromString('2023-01-11 02:00:00')
                        ],
                    ];
                }

                throw new RuntimeException();
            });



        $found = $this->calendarUtility->findClosestWorkingTime($point);

        $this->assertEquals(DateTime::fromString('2023-01-11 01:00:00'), $found);
    }

    public function testFindClosestWorkingTime2(): void
    {
        $time = DateTime::fromString('2023-01-01 00:00:00');

        $this->extractor
            ->expects($this->exactly(20))
            ->method('extract')
            ->willReturn([]);

        $found = $this->calendarUtility->findClosestWorkingTime($time);

        $this->assertEquals(null, $found);
    }

    public function testAddWorkingDays1(): void
    {
        $time = DateTime::fromString('2023-01-01 01:00:00');

        $point = $time->withTime(0, 0, 0)->modify('+1 day');

        $invokedCount = $this->exactly(2);

        $this->extractor
            ->expects($invokedCount)
            ->method('extractAllDay')
            ->willReturnCallback(function ($calendar, $a, $b) use ($invokedCount, $point) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals($this->calendar, $calendar);
                    $this->assertEquals($point, $a);
                    $this->assertEquals($point->modify('+30 days'), $b);

                    return [];
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals($this->calendar, $calendar);
                    $this->assertEquals($point->modify('+30 days'), $a);
                    $this->assertEquals($point->modify('+60 days'), $b);

                    return [
                        [
                            DateTime::fromString('2023-04-01 00:00:00'),
                            DateTime::fromString('2023-04-01 00:00:00')
                        ],
                    ];
                }

                throw new RuntimeException();
            });

        $found = $this->calendarUtility->addWorkingDays($time, 1);

        $this->assertEquals(DateTime::fromString('2023-04-01 00:00:00'), $found);
    }

    public function testAddWorkingDays2(): void
    {
        $time = DateTime::fromString('2023-01-01 01:00:00');

        $point = $time->withTime(0, 0, 0)->modify('+1 day');

        $invokedCount = $this->exactly(1);

        $this->extractor
            ->expects($invokedCount)
            ->method('extractAllDay')
            ->willReturnCallback(function ($calendar, $a, $b) use ($invokedCount, $point) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals($this->calendar, $calendar);
                    $this->assertEquals($point, $a);
                    $this->assertEquals($point->modify('+30 days'), $b);

                    return                 [
                        [
                            DateTime::fromString('2023-01-02 00:00:00'),
                            DateTime::fromString('2023-01-03 00:00:00')
                        ],
                        [
                            DateTime::fromString('2023-01-03 00:00:00'),
                            DateTime::fromString('2023-01-04 00:00:00')
                        ],
                    ];
                }

                throw new RuntimeException();
            });

        $found = $this->calendarUtility->addWorkingDays($time, 2);

        $this->assertEquals(DateTime::fromString('2023-01-03 00:00:00'), $found);
    }
}
