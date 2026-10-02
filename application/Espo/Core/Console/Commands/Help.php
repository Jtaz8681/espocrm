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

use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;

/**
 * @noinspection PhpUnused
 */
class Help implements Command
{
    public function __construct(private Metadata $metadata)
    {}

    public function run(Params $params, IO $io): void
    {
        /** @var string[] $fullCommandList */
        $fullCommandList = array_keys($this->metadata->get(['app', 'consoleCommands']) ?? []);

        $commandList = array_filter(
            $fullCommandList,
            function ($item): bool {
                return (bool) $this->metadata->get(['app', 'consoleCommands', $item, 'listed']);
            }
        );

        sort($commandList);

        $io->writeLine("");
        $io->writeLine("Available commands:");
        $io->writeLine("");

        foreach ($commandList as $item) {
            $io->writeLine(
                ' ' . Util::camelCaseToHyphen($item)
            );
        }

        $io->writeLine("");

        $io->writeLine("Usage:");
        $io->writeLine("");
        $io->writeLine(" bin/command [command-name] [some-argument] [--some-option=value] [--some-flag]");

        $io->writeLine("");

        $io->writeLine("Documentation: https://docs.espocrm.com/administration/commands/");

        $io->writeLine("");
    }
}
