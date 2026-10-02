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
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Util;
use Espo\Tools\ConsoleAppInfo\InfoProvider;

/**
 * @noinspection PhpUnused
 */
class AppInfo implements Command
{
    public function __construct(private InjectableFactory $injectableFactory, private FileManager $fileManager)
    {}

    public function run(Params $params, IO $io): void
    {
        /** @var string[] $fileList */
        $fileList = $this->fileManager->getFileList('application/Espo/Classes/AppInfo');

        $typeList = array_map(
            function ($item): string {
                return lcfirst(substr($item, 0, -4));
            },
            $fileList
        );

        foreach ($typeList as $type) {
            if ($params->hasFlag($type)) {
                $this->processType($io, $type, $params);

                return;
            }
        }

        if (count($params->getFlagList()) === 0) {
            $io->writeLine("");
            $io->writeLine("Available flags:");
            $io->writeLine("");

            foreach ($typeList as $type) {
                $io->writeLine(' --' . Util::camelCaseToHyphen($type));
            }

            $io->writeLine("");

            return;
        }

        $io->writeLine("Not supported flag specified.");
        $io->setExitStatus(1);
    }

    protected function processType(IO $io, string $type, Params $params): void
    {
        /** @var class-string<InfoProvider> $className */
        $className = 'Espo\\Classes\\AppInfo\\' . ucfirst($type);

        $provider = $this->injectableFactory->create($className);

        $result = $provider->get($params);

        $io->write($result);
        $io->writeLine("");
    }
}
