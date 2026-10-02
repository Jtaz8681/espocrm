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

namespace Espo\Modules\Crm\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\DateTime;
use Espo\Core\Utils\Log;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\Modules\Crm\Tools\MassEmail\QueueCreator;
use Espo\Modules\Crm\Tools\MassEmail\SendingProcessor;
use Throwable;

/**
 * @noinspection PhpUnused
 */
class ProcessMassEmail implements JobDataLess
{
    public function __construct(
        private SendingProcessor $processor,
        private QueueCreator $queue,
        private EntityManager $entityManager,
        private Log $log
    ) {}

    public function run(): void
    {
        $this->processCreateQueue();
        $this->processSend();
    }

    private function processCreateQueue(): void
    {
        $pendingMassEmails = $this->entityManager
            ->getRDBRepositoryByClass(MassEmail::class)
            ->where([
                'status' => MassEmail::STATUS_PENDING,
                'startAt<=' => date(DateTime::SYSTEM_DATE_TIME_FORMAT),
            ])
            ->find();

        foreach ($pendingMassEmails as $massEmail) {
            try {
                $this->queue->create($massEmail);
            } catch (Throwable $e) {
                $this->log->error("Create queue error. {id}.", [
                    'id' => $massEmail->getId(),
                    'exception' => $e,
                ]);
            }
        }
    }

    private function processSend(): void
    {
        $inProcessMassEmails = $this->entityManager
            ->getRDBRepositoryByClass(MassEmail::class)
            ->where([
                'status' => MassEmail::STATUS_IN_PROCESS,
            ])
            ->find();

        foreach ($inProcessMassEmails as $massEmail) {
            try {
                $this->processor->process($massEmail);
            } catch (Throwable $e) {
                $this->log->error("Sending mass email error. {id}.", [
                    'id' => $massEmail->getId(),
                    'exception' => $e,
                ]);
            }
        }
    }
}
