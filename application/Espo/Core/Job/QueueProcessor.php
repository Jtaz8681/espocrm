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

use Espo\Core\Job\QueueProcessor\Picker;
use Espo\Entities\Job as JobEntity;
use Espo\Core\Job\QueueProcessor\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\System;
use Espo\Core\Job\Job\Status;

use Spatie\Async\Pool as AsyncPool;

class QueueProcessor
{
    private bool $noTableLocking;

    public function __construct(
        private QueueUtil $queueUtil,
        private JobRunner $jobRunner,
        private AsyncPoolFactory $asyncPoolFactory,
        private EntityManager $entityManager,
        private Picker $picker,
        ConfigDataProvider $configDataProvider
    ) {
        $this->noTableLocking = $configDataProvider->noTableLocking();
    }

    public function process(Params $params): void
    {
        $pool = $params->useProcessPool() ?
            $this->asyncPoolFactory->create() : null;

        foreach ($this->picker->pick($params) as $job) {
            $this->processJob($params, $job, $pool);
        }

        $pool?->wait();
    }

    private function processJob(Params $params, JobEntity $job, ?AsyncPool $pool = null): void
    {
        $noLock = $params->noLock();
        $lockTable = $job->getScheduledJobId() && !$noLock && !$this->noTableLocking;

        if ($lockTable) {
            // MySQL doesn't allow to lock non-existent rows. We resort to locking an entire table.
            $this->entityManager->getLocker()->lockExclusive(JobEntity::ENTITY_TYPE);
        }

        $skip = $this->toSkip($noLock, $job);

        if ($skip) {
            if ($lockTable) {
                $this->entityManager->getLocker()->rollback();
            }

            return;
        }

        $this->prepareJob($job, $pool);

        $this->entityManager->saveEntity($job);

        if ($lockTable) {
            $this->entityManager->getLocker()->commit();
        }

        $this->runJob($job, $pool);
    }

    private function toSkip(bool $noLock, JobEntity $job): bool
    {
        $skip = !$noLock && !$this->queueUtil->isJobPending($job->getId());

        if (
            !$skip &&
            $job->getScheduledJobId() &&
            $this->queueUtil->isScheduledJobRunning(
                $job->getScheduledJobId(),
                $job->getTargetId(),
                $job->getTargetType(),
                $job->getTargetGroup()
            )
        ) {
            $skip = true;
        }

        return $skip;
    }

    private function prepareJob(JobEntity $job, ?AsyncPool $pool): void
    {
        $job->setStartedAtNow();

        if ($pool) {
            $job->setStatus(Status::READY);

            return;
        }

        $job->setStatus(Status::RUNNING);
        $job->setPid(System::getPid());
    }

    private function runJob(JobEntity $job, ?AsyncPool $pool): void
    {
        if (!$pool) {
            $this->jobRunner->run($job);

            return;
        }

        $task = new JobTask($job->getId());

        $pool->add($task);
    }
}
