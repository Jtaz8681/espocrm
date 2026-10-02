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

use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\System;
use Espo\Core\Utils\Util;

/**
 * @noinspection PhpUnused
 */
class CheckFilePermissions implements Command
{
    public function __construct(
        private FileManager $fileManager,
        private System $system
    ) {}

    public function run(Params $params, IO $io): void
    {
        $io->writeLine("\nNote: Run this command under the web server user.\n");

        $io->writeLine('Writable:');
        $io->writeLine('');

        foreach ($this->fileManager->getPermissionUtils()->getWritableList() as $path) {
            $fullPath = Util::concatPath($this->system->getRootDir(), $path);

            $isWritable = $this->fileManager->isWritable($fullPath);

            $msg = " " . ($isWritable ? "OK" : "FAIL") . " : $path";

            if (!$isWritable) {
                $io->setExitStatus(1);
            }

            $io->writeLine($msg);
        }
    }
}
