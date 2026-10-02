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

namespace Espo\Core\Console\Commands;

use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\DataManager;
use Espo\Core\Exceptions\Error;
use Espo\Core\Utils\Database\Schema\RebuildMode;

/**
 * @noinspection PhpUnused
 */
class Rebuild implements Command
{
    public function __construct(private DataManager $dataManager)
    {}

    /**
     * @throws Error
     */
    public function run(Params $params, IO $io): void
    {
        $this->dataManager->rebuild();

        if ($params->hasFlag('hard')) {
            if (!$params->hasFlag('y')) {
                $message =
                    "Are you sure you want to run a hard DB rebuild? It will drop unused columns, " .
                    "decrease exceeding column lengths. It may take some time to process.\nType [Y] to proceed.";

                $io->writeLine($message);

                $input = $io->readLine();

                if (strtolower($input) !== 'y') {
                    return;
                }
            }

            $this->dataManager->rebuildDatabase(null, RebuildMode::HARD);
        }

        $io->writeLine("Rebuild has been done.");
    }
}
