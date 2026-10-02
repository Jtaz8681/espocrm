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

use Espo\Core\Console\IO;
use Espo\Core\DataManager;
use Espo\Core\Exceptions\Error;
use Espo\Core\Utils\Config\ConfigWriter;
use Exception;
use RuntimeException;

class Runner
{
    public function __construct(
        private ExtractedStepsProvider $stepsProvider,
        private VersionDataProvider $versionDataProvider,
        private StepRunner $stepRunner,
        private DataManager $dataManager,
        private ConfigWriter $configWriter
    ) {}

    /**
     * @throws Error
     */
    public function run(IO $io): void
    {
        $this->dataManager->clearCache();

        $version = $this->versionDataProvider->getPreviousVersion();
        $targetVersion = $this->versionDataProvider->getTargetVersion();

        if ($version === $targetVersion) {
            $io->writeLine("No migrations to run.");

            return;
        }

        $prepareSteps = $this->stepsProvider->getPrepare($version, $targetVersion);

        if ($prepareSteps !== []) {
            $io->writeLine("Running prepare migrations...");

            foreach ($prepareSteps as $step) {
                $this->runPrepareStep($io, $step);
            }
        }

        $afterSteps = $this->stepsProvider->getAfterUpgrade($version, $targetVersion);

        if ($afterSteps === []) {
            $io->writeLine("No migrations to run. Updating version...");

            $this->updateVersion($targetVersion);
            $this->dataManager->updateAppTimestamp();
            $this->dataManager->rebuild();

            $io->writeLine("Completed.");

            return;
        }

        $io->writeLine("Running after-upgrade migrations...");

        foreach ($afterSteps as $step) {
            $this->runAfterUpgradeStep($io, $step);
            $this->updateVersion(VersionUtil::stepToVersion($step));
        }

        $this->updateVersion($targetVersion);
        $this->dataManager->updateAppTimestamp();

        $io->writeLine("Completed.");
    }

    private function runAfterUpgradeStep(IO $io, string $step): void
    {
        $io->write("  $step...");

        $isSuccessful = $this->stepRunner->runAfterUpgrade($step);

        if ($isSuccessful) {
            $io->writeLine(" DONE");

            return;
        }

        $io->writeLine(" FAIL");

        throw new RuntimeException("Step process failed.");
    }

    private function runPrepareStep(IO $io, string $step): void
    {
        $io->write("    $step...");

        try {
            $this->stepRunner->runPrepare($step);
        } catch (Exception $e) {
            $io->writeLine(" FAIL");

            throw new RuntimeException($e->getMessage());
        }

        $io->writeLine(" DONE");
    }

    private function updateVersion(string $targetVersion): void
    {
        $this->configWriter->set('version', $targetVersion);
        $this->configWriter->save();
    }
}
