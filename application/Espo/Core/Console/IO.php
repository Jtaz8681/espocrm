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

use RuntimeException;

use const STDOUT;
use const STDERR;
use const PHP_EOL;

/**
 * Input/Output methods.
 */
class IO
{
    /**
     * @var int<0, 255>
     */
    private int $exitStatus = 0;

    /**
     * Write a string to the output.
     */
    public function write(string $string): void
    {
        fwrite(STDOUT, $string);
    }

    /**
     * Write a string followed by the current line terminator to the output.
     */
    public function writeLine(string $string): void
    {
        fwrite(STDOUT, $string . PHP_EOL);
    }

    /**
     * Write a string to the error output.
     *
     * @since 10.0.0
     * @noinspection PhpUnused
     */
    public function writeError(string $string): void
    {
        fwrite(STDERR, $string);
    }

    /**
     * Write a string followed by the current line terminator to the error output.
     *
     * @since 10.0.0
     */
    public function writeErrorLine(string $string): void
    {
        fwrite(STDERR, $string . PHP_EOL);
    }

    /**
     * Read a line from input. A string is trimmed.
     */
    public function readLine(): string
    {
        return $this->readLineInternal();
    }

    /**
     * Read a secret line from input. A string is trimmed.
     */
    public function readSecretLine(): string
    {
        return $this->readLineInternal(true);
    }

    private function readLineInternal(bool $secret = false): string
    {
        $resource = fopen('php://stdin', 'r');

        if ($resource === false) {
            throw new RuntimeException("Could not open stdin.");
        }

        if ($secret && !self::isWindows()) {
            shell_exec('stty -echo');
        }

        $readString = fgets($resource);

        if ($secret && !self::isWindows()) {
            shell_exec('stty echo');
        }

        if ($readString === false) {
            $readString = '';
        }

        $string = trim($readString);

        fclose($resource);

        return $string;
    }

    private static function isWindows(): bool
    {
        return strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0;
    }

    /**
     * Set exit-status.
     *
     * @param int<0, 255> $exitStatus
     *   - `0` - success;
     *   - `1` - error;
     *   - `127` - command not found;
     */
    public function setExitStatus(int $exitStatus): void
    {
        $this->exitStatus = $exitStatus;
    }

    /**
     * Get exit-status.
     *
     * @return int<0, 255>
     */
    public function getExitStatus(): int
    {
        return $this->exitStatus;
    }
}
