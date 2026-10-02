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

namespace Espo\Classes\ConsoleCommands;

use Espo\Core\Authentication\ConfigDataProvider as AuthenticationConfig;
use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\Upgrades\Migration\VersionDataProvider;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\Database\Helper;
use Exception;

/**
 * @noinspection PhpUnused
 */
class AppCheck implements Command
{
    public function __construct(
        private Helper $databaseHelper,
        private SystemConfig $systemConfig,
        private VersionDataProvider $versionDataProvider,
        private AuthenticationConfig $authenticationConfig,
    ) {}

    public function run(Params $params, IO $io): void
    {
        $this->versionMatch($io);
        $this->checkDb($io);
        $this->maintenanceIsOff($io);
        $this->cronEnabled($io);
    }

    private function versionMatch(IO $io): void
    {
        $io->write('Migration not needed: ');

        if ($this->systemConfig->getVersion() === $this->versionDataProvider->getTargetVersion()) {
            $this->writeOK($io);
        } else {
            $this->writeFail($io);
            $io->setExitStatus(1);
        }

        $io->writeLine('');
    }

    private function checkDb(IO $io): void
    {
        $io->write('Database: ');

        try {
            $this->databaseHelper->createPDO();

            $this->writeOK($io);
        } catch (Exception) {
            $this->writeFail($io);
            $io->setExitStatus(1);
        }

        $io->writeLine('');
    }

    private function maintenanceIsOff(IO $io): void
    {
        $io->write('Not in maintenance mode: ');

        if (!$this->authenticationConfig->isMaintenanceMode()) {
            $this->writeOK($io);
        } else {
            $this->writeFail($io);
            $io->setExitStatus(1);
        }

        $io->writeLine('');
    }

    private function cronEnabled(IO $io): void
    {
        $io->write('Cron is enabled: ');

        if ($this->systemConfig->isCronEnabled()) {
            $this->writeOK($io);
        } else {
            $this->writeFail($io);
            $io->setExitStatus(1);
        }

        $io->writeLine('');
    }

    private function writeOK(IO $io): void
    {
        $io->write("\033[32mOK\033[0m");
    }

    private function writeFail(IO $io): void
    {
        $io->write("\033[31mFAIL\033[0m");
    }
}
