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
use Espo\Core\Utils\Database\Helper;
use Exception;

/**
 * @noinspection PhpUnused
 */
class DbCheck implements Command
{
    public function __construct(
        private Helper $helper,
    ) {}

    public function run(Params $params, IO $io): void
    {
        try {
            $this->helper->createPDO();

            $io->writeLine('OK');
        } catch (Exception) {
            $io->writeErrorLine("Not connected");
            $io->setExitStatus(1);

            return;
        }
    }
}
