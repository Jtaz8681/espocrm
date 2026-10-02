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
use Espo\Tools\CategoryTree\RebuildPaths;
use Exception;

class RebuildCategoryPaths implements Command
{
    private RebuildPaths $rebuildPaths;

    public function __construct(RebuildPaths $rebuildPaths)
    {
        $this->rebuildPaths = $rebuildPaths;
    }

    public function run(Params $params, IO $io): void
    {
        $entityType = $params->getArgument(0);

        if (!$entityType) {
            $io->setExitStatus(1);
            $io->writeLine("Error: No entity type. Should be specified as the first argument.");

            return;
        }

        try {
            $this->rebuildPaths->run($entityType);
        } catch (Exception $e) {
            $io->setExitStatus(1);
            $io->writeLine("Error: " . $e->getMessage());

            return;
        }

        $io->writeLine("Done.");
    }
}
