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

use Espo\ORM\Name\Attribute;
use Spatie\Async\Task as AsyncTask;

use Espo\Core\Application;
use Espo\Core\Application\Runner\Params as RunnerParams;
use Espo\Core\ApplicationRunners\Job as JobRunner;
use Espo\Core\Utils\Log;

use Throwable;

class JobTask extends AsyncTask
{
    private string $jobId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @return void
     */
    public function configure()
    {}

    /**
     * @return void
     */
    public function run()
    {
        $app = new Application();

        $params = RunnerParams::create()->with(Attribute::ID, $this->jobId);

        try {
            $app->run(JobRunner::class, $params);
        } catch (Throwable $e) {
            $log = $app->getContainer()->getByClass(Log::class);

            $log->error("JobTask: Failed to run job '$this->jobId'. Error: " . $e->getMessage());
        }
    }
}
