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

namespace Espo\Core\Console;

use Espo\Core\ApplicationUser;
use Espo\Core\Console\Exceptions\InvalidArgument;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use Espo\Core\Console\Exceptions\CommandNotSpecified;
use Espo\Core\Console\Exceptions\CommandNotFound;
use Espo\Core\Console\Command\Params;

/**
 * Processes console commands.
 */
class CommandManager
{
    private const DEFAULT_COMMAND = 'Help';
    private const DEFAULT_COMMAND_FLAG = 'help';

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private ApplicationUser $applicationUser
    ) {}

    /**
     * @param array<int, string> $argv
     * @return int<0, 255> Exit-status.
     */
    public function run(array $argv): int
    {
        $command = $this->getCommandNameFromArgv($argv);
        $params = $this->createParamsFromArgv($argv);

        if (
            $command === null &&
            (
                $params->hasFlag(self::DEFAULT_COMMAND_FLAG) ||
                count($params->getFlagList()) === 0 &&
                count($params->getOptions()) === 0 &&
                count($params->getArgumentList()) === 0
            )
        ) {
            $command = self::DEFAULT_COMMAND;
        }

        if ($command === null) {
            throw new CommandNotSpecified("Command name is not specified.");
        }

        $this->checkParams($command, $params);

        $io = new IO();

        $this->setupUser($command);

        $commandObj = $this->createCommand($command);

        if (!$commandObj instanceof Command) {
            // for backward compatibility
            assert(method_exists($commandObj, 'run'));

            $commandObj->run($params->getOptions(), $params->getFlagList(), $params->getArgumentList());

            return 0;
        }

        $commandObj->run($params, $io);

        return $io->getExitStatus();
    }

    /**
     * @param array<int, string> $argv
     */
    private function getCommandNameFromArgv(array $argv): ?string
    {
        $command = isset($argv[1]) ? trim($argv[1]) : null;

        if ($command === null && count($argv) < 2) {
            return null;
        }

        if (!$command || !ctype_alpha($command[0])) {
            return null;
        }

        return ucfirst(Util::hyphenToCamelCase($command));
    }

    private function createCommand(string $command): object
    {
        $className = $this->getClassName($command);

        return $this->injectableFactory->create($className);
    }

    /**
     * @return class-string<Command>
     */
    private function getClassName(string $command): string
    {
        /** @var ?class-string<Command> $className */
        $className =
            $this->metadata->get(['app', 'consoleCommands', lcfirst($command), 'className']);

        if ($className) {
            return $className;
        }

        $className = 'Espo\\Core\\Console\\Commands\\' . $command;

        if (!class_exists($className)) {
            throw new CommandNotFound("Command '" . Util::camelCaseToHyphen($command) ."' does not exist.");
        }

        /** @var class-string<Command> */
        return $className;
    }

    /**
     * @param array<int, string> $argv
     */
    private function createParamsFromArgv(array $argv): Params
    {
        return Params::fromArgs(array_slice($argv, 1));
    }

    private function setupUser(string $command): void
    {
        $noSystemUser = $this->metadata->get(['app', 'consoleCommands', lcfirst($command), 'noSystemUser']);

        if ($noSystemUser) {
            return;
        }

        $this->applicationUser->setupSystemUser();
    }

    private function checkParams(string $command, Params $params): void
    {
        $this->checkOptions($command, $params);
        $this->checkFlags($command, $params);
    }

    private function checkOptions(string $command, Params $params): void
    {
        $allowedOptions = $this->metadata->get(['app', 'consoleCommands', lcfirst($command), 'allowedOptions']);

        if (!is_array($allowedOptions)) {
            return;
        }

        $notAllowedOptions = array_diff(array_keys($params->getOptions()), $allowedOptions);

        if ($notAllowedOptions === []) {
            return;
        }

        $msg = sprintf("Not allowed options: %s.", implode(', ', $notAllowedOptions));

        throw new InvalidArgument($msg);
    }

    private function checkFlags(string $command, Params $params): void
    {
        $allowedFlags = $this->metadata->get(['app', 'consoleCommands', lcfirst($command), 'allowedFlags']);

        if (!is_array($allowedFlags)) {
            return;
        }

        $notAllowedFlags = array_diff($params->getFlagList(), $allowedFlags);

        if ($notAllowedFlags === []) {
            return;
        }

        $msg = sprintf("Not allowed flags: %s.", implode(', ', $notAllowedFlags));

        throw new InvalidArgument($msg);
    }
}
