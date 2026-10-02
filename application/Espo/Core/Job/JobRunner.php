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

use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\System;
use Espo\Core\Job\Job\Data;
use Espo\Core\Job\Job\Status;
use Espo\Entities\Job as JobEntity;
use Espo\ORM\Name\Attribute;
use LogicException;
use RuntimeException;
use Throwable;

class JobRunner
{
    public function __construct(
        private JobFactory $jobFactory,
        private ScheduleUtil $scheduleUtil,
        private EntityManager $entityManager,
        private Log $log,
    ) {}

    /**
     * Run a job entity. Does not throw exceptions.
     */
    public function run(JobEntity $jobEntity): void
    {
        try {
            $this->runInternal($jobEntity);
        } catch (Throwable $e) {
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * Run a job entity. Throws exceptions.
     *
     * @throws Throwable
     */
    public function runThrowingException(JobEntity $jobEntity): void
    {
        $this->runInternal($jobEntity, true);
    }

    /**
     * Run a job by ID. A job must have status 'Ready'.
     * Used when running jobs in parallel processes.
     */
    public function runById(string $id): void
    {
        if ($id === '') {
            throw new RuntimeException("Empty job ID.");
        }

        $jobEntity = $this->entityManager->getRDBRepositoryByClass(JobEntity::class)->getById($id);

        if (!$jobEntity) {
            throw new RuntimeException("Job '$id' not found.");
        }

        if ($jobEntity->getStatus() !== Status::READY) {
            throw new RuntimeException("Can't run job '$id' with not Ready status.");
        }

        $this->setJobRunning($jobEntity);
        $this->run($jobEntity);
    }

    /**
     * @throws Throwable
     */
    private function runInternal(JobEntity $jobEntity, bool $throwException = false): void
    {
        $isSuccess = true;
        $exception = null;

        if ($jobEntity->getStatus() !== Status::RUNNING) {
            $this->setJobRunning($jobEntity);
        }

        try {
            if ($jobEntity->getScheduledJobId()) {
                $this->runScheduledJob($jobEntity);
            } else if ($jobEntity->getJob()) {
                $this->runJobNamed($jobEntity);
            } else if ($jobEntity->getClassName()) {
                $this->runJobWithClassName($jobEntity);
            } else {
                $id = $jobEntity->getId();

                throw new RuntimeException("Not runnable job '$id'.");
            }
        } catch (Throwable $e) {
            $isSuccess = false;

            $jobId = $jobEntity->hasId() ? $jobEntity->getId() : null;

            $this->log->critical("Failed job {id}.", [
                'exception' => $e,
                Attribute::ID => $jobId,
            ]);

            if ($throwException) {
                $exception = $e;
            }
        }

        $status = $isSuccess ? Status::SUCCESS : Status::FAILED;

        $jobEntity->setStatus($status);

        if ($isSuccess) {
            $jobEntity->setExecutedAtNow();
        }

        $this->entityManager->saveEntity($jobEntity);

        if ($throwException && $exception) {
            throw new $exception($exception->getMessage());
        }

        if ($jobEntity->getScheduledJobId()) {
            $this->scheduleUtil->addLogRecord(
                scheduledJobId: $jobEntity->getScheduledJobId(),
                status: $status,
                targetId: $jobEntity->getTargetId(),
                targetType: $jobEntity->getTargetType(),
            );
        }
    }

    private function runJobNamed(JobEntity $jobEntity): void
    {
        $jobName = $jobEntity->getJob();

        if (!$jobName) {
            throw new RuntimeException("No job name.");
        }

        $job = $this->jobFactory->create($jobName);

        $this->runJob($job, $jobEntity);
    }

    private function runScheduledJob(JobEntity $jobEntity): void
    {
        $jobName = $jobEntity->getScheduledJobJob();

        if (!$jobName) {
            throw new RuntimeException("Can't run job '{$jobEntity->getId()}'. Not a scheduled job.");
        }

        $job = $this->jobFactory->create($jobName);

        $this->runJob($job, $jobEntity);
    }

    private function runJobWithClassName(JobEntity $jobEntity): void
    {
        $className = $jobEntity->getClassName();

        if (!$className) {
            throw new RuntimeException("No className in job {$jobEntity->getId()}.");
        }

        $job = $this->jobFactory->createByClassName($className);

        $this->runJob($job, $jobEntity);
    }

    private function runJob(Job|JobDataLess $job, JobEntity $jobEntity): void
    {
        if ($job instanceof JobDataLess) {
            $job->run();

            return;
        }

        $data = Data::create($jobEntity->getData())
            ->withTargetId($jobEntity->getTargetId())
            ->withTargetType($jobEntity->getTargetType());

        $job->run($data);
    }

    private function setJobRunning(JobEntity $jobEntity): void
    {
        if (!$jobEntity->getStartedAt()) {
            $jobEntity->setStartedAtNow();
        }

        $jobEntity->setStatus(Status::RUNNING);
        $jobEntity->setPid(System::getPid());

        $this->entityManager->saveEntity($jobEntity);
    }
}
