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
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Json;

/**
 * @noinspection PhpUnused
 */
class GetConfigParam implements Command
{
    public function __construct(
        private Config $config,
    ) {}

    public function run(Params $params, IO $io): void
    {
        $param = $params->getArgument(0) ?? null;

        if ($param === null) {
            $io->writeLine("Parameter name is not specified.");
            $io->setExitStatus(1);

            return;
        }

        if (!$this->config->has($param)) {
            $io->writeLine("Parameter '$param' is not set in config.");
            $io->setExitStatus(1);

            return;
        }

        $isJson = $params->hasFlag('json');

        $value = $this->config->get($param);

        $value = $this->formatValue($value, $isJson);

        $io->writeLine($value);
    }

    private function formatValue(mixed $value, bool $isJson): string
    {
        if ($isJson) {
            return self::toJson($value);
        }

        return match (true) {
            $value === true => 'true',
            $value === false => 'false',
            $value === null => 'null',
            is_scalar($value) => (string) $value,
            default => self::toJson($value),
        };
    }

    private static function toJson(mixed $value): string
    {
        return Json::encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
