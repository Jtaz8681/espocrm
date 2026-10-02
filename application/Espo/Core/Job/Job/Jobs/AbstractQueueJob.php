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

namespace Espo\Core\Job\Job\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Job\JobManager;
use Espo\Core\Job\QueuePortionNumberProvider;

abstract class AbstractQueueJob implements JobDataLess
{
    protected string $queue;

    public function __construct(
        private JobManager $jobManager,
        private QueuePortionNumberProvider $portionNumberProvider)
    {}

    public function run(): void
    {
        $limit = $this->portionNumberProvider->get($this->queue);

        $this->jobManager->processQueue($this->queue, $limit);
    }
}
