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

use Espo\Core\Job\QueueProcessor\Params;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Log;
use Espo\Entities\Job as JobEntity;

use RuntimeException;
use Throwable;

/**
 * Handles processing jobs.
 */
class JobManager
{
    private bool $useProcessPool = false;
    protected string $lastRunTimeFile = 'data/cache/application/cronLastRunTime.php';

    public function __construct(
        private FileManager $fileManager,
        private JobRunner $jobRunner,
        private Log $log,
        private ScheduleProcessor $scheduleProcessor,
        private QueueUtil $queueUtil,
        private AsyncPoolFactory $asyncPoolFactory,
        private QueueProcessor $queueProcessor,
        private ConfigDataProvider $configDataProvider
    ) {
        if ($this->configDataProvider->runInParallel()) {
            if ($this->asyncPoolFactory->isSupported()) {
                $this->useProcessPool = true;
            } else {
                $this->log->warning("Enabled `jobRunInParallel` parameter requires pcntl and posix extensions.");
            }
        }
    }

    /**
     * Process jobs. Jobs will be created according scheduling. Then pending jobs will be processed.
     * This method supposed to be called on every Cron run or loop iteration of the Daemon.
     */
    public function process(): void
    {
        if (!$this->checkLastRunTime()) {
            $this->log->info('JobManager: Skip job processing. Too frequent execution.');

            return;
        }

        $this->updateLastRunTime();
        $this->queueUtil->markJobsFailed();
        $this->queueUtil->updateFailedJobAttempts();
        $this->scheduleProcessor->process();
        $this->queueUtil->removePendingJobDuplicates();
        $this->processMainQueue();
    }

    /**
     * Process pending jobs from a specific queue. Jobs within a queue are processed one by one.
     */
    public function processQueue(string $queue, int $limit): void
    {
        $params = Params
            ::create()
            ->withQueue($queue)
            ->withLimit($limit)
            ->withUseProcessPool(false)
            ->withNoLock(true);

        $this->queueProcessor->process($params);
    }

    /**
     * Process pending jobs from a specific group. Jobs within a group are processed one by one.
     */
    public function processGroup(string $group, int $limit): void
    {
        $params = Params
            ::create()
            ->withGroup($group)
            ->withLimit($limit)
            ->withUseProcessPool(false)
            ->withNoLock(true);

        $this->queueProcessor->process($params);
    }

    private function processMainQueue(): void
    {
        $limit = $this->configDataProvider->getMaxPortion();

        $params = Params
            ::create()
            ->withUseProcessPool($this->useProcessPool)
            ->withLimit($limit);

        $subQueueParams = [
            $params->withWeight(0.5),
            $params->withQueue(QueueName::M0)->withWeight(0.5),
        ];

        $params = $params->withSubQueueParamsList($subQueueParams);

        $this->queueProcessor->process($params);
    }

    /**
     * Run a specific job by ID. A job status should be set to 'Ready'.
     */
    public function runJobById(string $id): void
    {
        $this->jobRunner->runById($id);
    }

    /**
     * Run a specific job.
     *
     * @throws Throwable
     */
    public function runJob(JobEntity $job): void
    {
        $this->jobRunner->runThrowingException($job);
    }

    /**
     * @todo Move to a separate class.
     */
    private function getLastRunTime(): int
    {
        if ($this->fileManager->isFile($this->lastRunTimeFile)) {
            try {
                $data = $this->fileManager->getPhpContents($this->lastRunTimeFile);
            } catch (RuntimeException) {
                $data = null;
            }

            if (is_array($data) && isset($data['time'])) {
                return (int) $data['time'];
            }
        }

        return time() - $this->configDataProvider->getCronMinInterval() - 1;
    }

    /**
     * @todo Move to a separate class.
     */
    private function updateLastRunTime(): void
    {
        $data = ['time' => time()];

        $this->fileManager->putPhpContents($this->lastRunTimeFile, $data, false, true);
    }

    private function checkLastRunTime(): bool
    {
        $currentTime = time();
        $lastRunTime = $this->getLastRunTime();

        $cronMinInterval = $this->configDataProvider->getCronMinInterval();

        if ($currentTime > ($lastRunTime + $cronMinInterval)) {
            return true;
        }

        return false;
    }
}
