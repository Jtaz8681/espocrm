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

namespace Espo\Core\ApplicationRunners;

use Espo\Core\Application\Runner;
use Espo\Core\Job\JobManager;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\Log;

/**
 * Runs Cron.
 */
class Cron implements Runner
{
    use Cli;
    use SetupSystemUser;

    public function __construct(
        private JobManager $jobManager,
        private SystemConfig $config,
        private Log $log
    ) {}

    public function run(): void
    {
        if (!$this->config->isCronEnabled()) {
            $this->log->warning("Cron is not run because it's disabled with 'cronDisabled' param.");

            return;
        }

        $this->jobManager->process();
    }
}
