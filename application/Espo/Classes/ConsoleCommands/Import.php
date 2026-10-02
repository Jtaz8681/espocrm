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

use Espo\Tools\Import\Service;

use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;

use Throwable;

class Import implements Command
{
    public function __construct(private Service $service, private FileManager $fileManager)
    {}

    public function run(Params $params, IO $io) : void
    {
        $id = $params->getOption('id');
        $filePath = $params->getOption('file');
        $paramsId = $params->getOption('paramsId');

        $forceResume = $params->hasFlag('resume');
        $revert = $params->hasFlag('revert');

        if (!$id && $filePath) {
            if (!$paramsId) {
                $io->writeLine("You need to specify --params-id option.");
                $io->setExitStatus(1);

                return;
            }

            if (!$this->fileManager->isFile($filePath)) {
                $io->writeLine("File not found.");
                $io->setExitStatus(1);

                return;
            }

            $contents = $this->fileManager->getContents($filePath);

            try {
                $result = $this->service->importContentsWithParamsId($contents, $paramsId);

                $resultId = $result->getId();
                $countCreated = $result->getCountCreated();
                $countUpdated = $result->getCountUpdated();
                $countError = $result->getCountError();
                $countDuplicate = $result->getCountDuplicate();
            } catch (Throwable $e) {
                $io->writeLine("Error occurred: " . $e->getMessage());
                $io->setExitStatus(1);

                return;
            }

            $io->writeLine("Finished.");
            $io->writeLine("  Import ID: {$resultId}");
            $io->writeLine("  Created: {$countCreated}");
            $io->writeLine("  Updated: {$countUpdated}");
            $io->writeLine("  Duplicates: {$countDuplicate}");
            $io->writeLine("  Errors: {$countError}");

            return;
        }

        if ($id && $revert) {
            $io->writeLine("Reverting import...");

            try {
                $this->service->revert($id);
            } catch (Throwable $e) {
                $io->writeLine("Error occurred: " . $e->getMessage());
                $io->setExitStatus(1);

                return;
            }

            $io->writeLine("Finished.");

            return;
        }

        if ($id) {
            $io->writeLine("Running import, this may take a while...");

            try {
                $result = $this->service->importById($id, true, $forceResume);
            } catch (Throwable $e) {
                $io->writeLine("Error occurred: " . $e->getMessage());
                $io->setExitStatus(1);

                return;
            }

            $countCreated = $result->getCountCreated();
            $countUpdated = $result->getCountUpdated();
            $countError = $result->getCountError();
            $countDuplicate = $result->getCountDuplicate();

            $io->writeLine("Finished.");
            $io->writeLine("  Created: {$countCreated}");
            $io->writeLine("  Updated: {$countUpdated}");
            $io->writeLine("  Duplicates: {$countDuplicate}");
            $io->writeLine("  Errors: {$countError}");

            return;
        }

        $io->writeLine("Not enough params passed.");
        $io->setExitStatus(1);
    }
}
