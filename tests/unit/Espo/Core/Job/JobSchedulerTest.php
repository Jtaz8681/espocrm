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

namespace tests\unit\Espo\Core\Job;

use Espo\Core\Field\DateTime as DateTimeField;
use Espo\Core\Job\JobScheduler;
use Espo\Core\Job\JobScheduler\Creator;
use Espo\Core\Job\QueueName;
use Espo\Core\Job\Job\Data;
use PHPUnit\Framework\TestCase;
use tests\unit\testClasses\Core\Job\TestJob;

use DateTimeImmutable;
use DateInterval;

class JobSchedulerTest extends TestCase
{
    private ?JobScheduler\Creator $creator = null;

    protected function setUp(): void
    {
        $this->creator = $this->createMock(JobScheduler\Creator::class);
    }

    public function testSchedule1(): void
    {
        $scheduler = new JobScheduler($this->creator);

        $time = new DateTimeImmutable();

        $delay = DateInterval::createFromDateString('1 minute');

        $expectedData = new Creator\Data(
            className: TestJob::class,
            queue: QueueName::Q0,
            group: null,
            data: new Data((object) ['test' => '1']),
            time: DateTimeField::fromDateTime($time)->addMinutes(1),
        );

        $this->creator
            ->expects($this->once())
            ->method('create')
            ->with($expectedData);

        $scheduler
            ->setClassName(TestJob::class)
            ->setQueue(QueueName::Q0)
            ->setData([
                'test' => '1',
            ])
            ->setTime($time)
            ->setDelay($delay)
            ->schedule();
    }

    public function testSchedule2(): void
    {
        $scheduler = new JobScheduler($this->creator);

        $time = new DateTimeImmutable();

        $expectedData = new Creator\Data(
            className: TestJob::class,
            queue: null,
            group: 'g-1',
            data: (new Data((object) ['test' => '1']))->withTargetType('TestType')->withTargetId('test-id'),
            time: DateTimeField::fromDateTime($time),
        );

        $this->creator
            ->expects($this->once())
            ->method('create')
            ->with($expectedData);

        $data = Data
            ::create([
                'test' => '1',
            ])
            ->withTargetId('test-id')
            ->withTargetType('TestType');

        $scheduler
            ->setClassName(TestJob::class)
            ->setGroup('g-1')
            ->setData($data)
            ->setTime($time)
            ->schedule();
    }
}
