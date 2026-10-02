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
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\ObjectUtil;
use Exception;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class SetConfigParam implements Command
{
    private const string TYPE_STRING = 'string';
    private const string TYPE_BOOL = 'bool';
    private const string TYPE_INT = 'int';
    private const string TYPE_FLOAT = 'float';
    private const string TYPE_JSON = 'json';
    private const string TYPE_AUTO = 'auto';

    /** @var string[] */
    private const array ALLOWED_TYPES = [
        self::TYPE_STRING,
        self::TYPE_BOOL,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_JSON,
        self::TYPE_AUTO,
    ];

    public function __construct(
        private Config $config,
        private ConfigWriter $configWriter,
    ) {}

    public function run(Params $params, IO $io): void
    {
        $type = $params->getOption('type') ?? self::TYPE_STRING;

        if (!in_array($type, self::ALLOWED_TYPES)) {
            $io->writeLine("Not allowed type.");
            $io->setExitStatus(1);

            return;
        }

        $name = $params->getArgument(0) ?? null;
        $value = $params->getArgument(1) ?? null;

        if ($name === null) {
            $io->writeLine("Parameter name and value are not specified.");
            $io->setExitStatus(1);

            return;
        }

        if ($value === null) {
            $io->writeLine("Value is not specified.");
            $io->setExitStatus(1);

            return;
        }

        $value = $this->prepareValue($io, $value, $type);

        if ($io->getExitStatus() !== 0) {
            return;
        }

        if (str_contains($name, '.')) {
            if (substr_count($name, '.') > 1) {
                $io->writeLine("Cannot use a parameter name with more than one dot.");
                $io->setExitStatus(1);

                return;
            }

            [$nameOne, $subName] = explode('.', $name);

            if ($nameOne === '' || $subName === '') {
                $io->writeLine("Bad parameter name.");
                $io->setExitStatus(1);

                return;
            }

            $setValue = $this->config->get($nameOne) ?? [];

            if ($setValue instanceof stdClass) {
                $setValue = ObjectUtil::clone($setValue);

                $setValue->$subName = $value;
            } else if (is_array($setValue) && !array_is_list($setValue)) {
                $setValue[$subName] = $value;
            } else {
                $io->writeLine("No parameter parameter '$nameOne' in config.");
                $io->setExitStatus(1);

                return;
            }

            $value = $setValue;
            $name = $nameOne;
        }

        $this->configWriter->set($name, $value);
        $this->configWriter->save();
    }

    private function prepareValue(IO $io, string $value, string $type): mixed
    {
        if ($type === self::TYPE_AUTO) {
            $lower = strtolower($value);

            if ($lower === 'true') {
                return true;
            }

            if ($lower === 'false') {
                return false;
            }

            if ($lower === 'null') {
                return null;
            }

            if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
                return (int) $value;
            }

            if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
                return (float) $value;
            }

            return $value;
        }

        if ($type === self::TYPE_JSON) {
            try {
                return Json::decode($value);
            } catch (Exception) {
                $io->writeLine("Invalid JSON.");
                $io->setExitStatus(1);

                return null;
            }
        }

        if ($type === self::TYPE_INT) {
            if (!is_numeric($value)) {
                $io->writeLine("Value is not numeric.");
                $io->setExitStatus(1);

                return null;
            }

            return (int) $value;
        }

        if ($type === self::TYPE_FLOAT) {
            if (!is_numeric($value)) {
                $io->writeLine("Value is not numeric.");
                $io->setExitStatus(1);

                return null;
            }

            return (float) $value;
        }

        if ($type === self::TYPE_BOOL) {
            $value = strtolower($value);

            if (!in_array($value, ['true', 'false', '1', '0'])) {
                $io->writeLine("Value is not boolean.");
                $io->setExitStatus(1);

                return null;
            }

            return $value === 'true' || $value === '1';
        }

        return $value;
    }
}
