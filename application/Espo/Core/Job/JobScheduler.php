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

namespace Espo\Core\Job;

use Espo\Core\Field\DateTime as DateTimeField;
use Espo\Core\Job\Job\Data;
use Espo\Core\Job\JobScheduler\Creator;

use ReflectionClass;
use DateTimeInterface;
use DateTimeImmutable;
use DateInterval;
use RuntimeException;
use TypeError;

/**
 * Creates jobs in a queue.
 */
class JobScheduler
{
    /** @var ?class-string<Job|JobDataLess> */
    private ?string $className = null;
    private ?string $queue = null;
    private ?string $group = null;
    private ?Data $data = null;
    private ?DateTimeImmutable $time = null;
    private ?DateInterval $delay = null;

    public function __construct(
        private Creator $creator,
    ) {}

    /**
     * A class name of the job. Should implement the `Job` interface.
     *
     * @param class-string<Job|JobDataLess> $className
     */
    public function setClassName(string $className): self
    {
        if (!class_exists($className)) {
            throw new RuntimeException("Class '$className' does not exist.");
        }

        $class = new ReflectionClass($className);

        if (
            !$class->implementsInterface(Job::class) &&
            !$class->implementsInterface(JobDataLess::class)
        ) {
            throw new RuntimeException("Class '$className' does not implement 'Job' or 'JobDataLess' interface.");
        }

        $this->className = $className;

        return $this;
    }

    /**
     * In what queue to run the job.
     *
     * @param ?string $queue A queue name. Available names are defined in the `QueueName` class.
     */
    public function setQueue(?string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * In what group to run the job. Jobs within a group will run one-by-one. Jobs with different group
     * can run in parallel. The job can't have both queue and group set.
     *
     * @param ?string $group A group. Any string ID value can be used as a group name. E.g. a user ID.
     */
    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Set an execution time. If not set, then the current time will be used.
     */
    public function setTime(?DateTimeInterface $time): self
    {
        $timeCopy = $time;

        if (!is_null($time) && !$time instanceof DateTimeImmutable) {
            /** @noinspection PhpParamsInspection */
            $timeCopy = DateTimeImmutable::createFromMutable($time);
        }

        /** @var ?DateTimeImmutable $timeCopy */

        $this->time = $timeCopy;

        return $this;
    }

    /**
     * Set an execution delay.
     */
    public function setDelay(?DateInterval $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Set data to be passed to the job.
     *
     * @param Data|array<string, mixed>|null $data
     */
    public function setData($data): self
    {
        /** @var mixed $data */

        if (!is_null($data) && !is_array($data) && !$data instanceof Data) {
            throw new TypeError();
        }

        if (!$data instanceof Data) {
            $data = Data::create($data);
        }

        $this->data = $data;

        return $this;
    }

    public function schedule(): void
    {
        if (!$this->className) {
            throw new RuntimeException("Class name is not set.");
        }

        if ($this->group && $this->queue) {
            throw new RuntimeException("Can't have both queue and group.");
        }

        $time = $this->time;

        if (!$this->time && $this->delay) {
            $time = new DateTimeImmutable();
        }

        if ($time && $this->delay) {
            $time = $time->add($this->delay);
        }

        $data = $this->data ?? Data::create();

        $creatorData = new Creator\Data(
            className: $this->className,
            queue: $this->queue,
            group: $this->group,
            data: $data,
            time: $time ? DateTimeField::fromDateTime($time) : null,
        );

        $this->creator->create($creatorData);
    }
}
