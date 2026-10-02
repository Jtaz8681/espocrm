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
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs daemon. The daemon runs the cron more often than once a minute.
 */
class Daemon implements Runner
{
    use Cli;

    public function __construct(private Config $config, private Log $log)
    {}

    public function run(): void
    {
        $maxProcessNumber = $this->config->get('daemonMaxProcessNumber');
        $interval = $this->config->get('daemonInterval');
        $timeout = $this->config->get('daemonProcessTimeout');

        $phpExecutablePath = $this->config->get('phpExecutablePath');

        if (!$phpExecutablePath) {
            $phpExecutablePath = (new PhpExecutableFinder)->find();
        }

        if (!$maxProcessNumber || !$interval) {
            $this->log->error("Daemon config params are not set.");

            return;
        }

        $processList = [];

        while (true) { /** @phpstan-ignore-line */
            $toSkip = false;
            $runningCount = 0;

            foreach ($processList as $i => $process) {
                if ($process->isRunning()) {
                    $runningCount++;
                } else {
                    unset($processList[$i]);
                }
            }

            $processList = array_values($processList);

            if ($runningCount >= $maxProcessNumber) {
                $toSkip = true;
            }

            if (!$toSkip) {
                $process = new Process([$phpExecutablePath, 'cron.php']);

                $process->setTimeout($timeout);

                $process->start();

                $processList[] = $process;
            }

            sleep($interval);
        }
    }
}
