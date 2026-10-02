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

namespace Espo\Core\Upgrades\Migration;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class StepRunner
{
    public function __construct(
        private Config $config,
        private InjectableFactory $injectableFactory,
        private Log $log
    ) {}

    public function runAfterUpgrade(string $step): bool
    {
        $phpExecutablePath = $this->getPhpExecutablePath();

        $command = "command.php";

        $process = new Process([$phpExecutablePath, $command, 'migration-version-step', "--step=$step"]);
        $process->setTimeout(null);
        $process->run();

        $this->processLogging($process);

        return $process->isSuccessful();
    }

    public function runPrepare(string $step): void
    {
        $dir = 'V' . str_replace('.', '_', $step);

        $className = "Espo\\Core\\Upgrades\\Migrations\\$dir\\Prepare";

        if (!class_exists($className)) {
            throw new RuntimeException("No prepare script $step.");
        }

        /** @var Script $script */
        $script = $this->injectableFactory->create($className);
        $script->run();
    }

    private function getPhpExecutablePath(): string
    {
        $phpExecutablePath = $this->config->get('phpExecutablePath');

        if (!$phpExecutablePath) {
            $phpExecutablePath = (new PhpExecutableFinder)->find();
        }

        return $phpExecutablePath;
    }

    private function processLogging(Process $process): void
    {
        if ($process->isSuccessful()) {
            return;
        }

        $output = $process->getOutput();

        if ($output) {
            $this->log->error("Migration step command: " . $output);
        }
    }
}
