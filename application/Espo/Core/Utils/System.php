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

namespace Espo\Core\Utils;

use Symfony\Component\Process\PhpExecutableFinder;

class System
{
    /**
     * Get a web server name.
     *
     * @return string E.g. `microsoft-iis`, `nginx`, `apache`.
     */
    public function getServerType(): string
    {
        $serverSoft = $_SERVER['SERVER_SOFTWARE'];

        preg_match('/^(.*?)\//i', $serverSoft, $match);

        if (empty($match[1])) {
            preg_match('/^(.*)\/?/i', $serverSoft, $match);
        }

        return strtolower(
            trim($match[1]) /** @phpstan-ignore-line */
        );
    }

    /**
     * Get an OS. Details at http://en.wikipedia.org/wiki/Uname.
     *
     * @return ?string E.g. `windows`, `mac`, `linux`.
     */
    public function getOS(): ?string
    {
        $osList = [
            'windows' => [
                'win',
                'UWIN',
            ],
            'mac' => [
                'mac',
                'darwin',
            ],
            'linux' => [
                'linux',
                'cygwin',
                'GNU',
                'FreeBSD',
                'OpenBSD',
                'NetBSD',
            ],
        ];

        $sysOS = strtolower(PHP_OS);

        foreach ($osList as $osName => $osSystem) {
            if (preg_match('/^('.implode('|', $osSystem).')/i', $sysOS)) {
                return $osName;
            }
        }

        return null;
    }

    /**
     * Get a root directory of BugZyro.
     */
    public function getRootDir(): string
    {
        $bPath = realpath('bootstrap.php') ?: '';

        return dirname($bPath);
    }

    /**
     * Get a PHP binary.
     */
    public function getPhpBinary(): ?string
    {
        $path = (new PhpExecutableFinder)->find();

        if ($path === false) {
            return null;
        }

        return $path;
    }

    /**
     * Get PHP version (only digits and dots).
     */
    public static function getPhpVersion(): string
    {
        $version = phpversion();

        $matches = null;

        if (preg_match('/^[0-9\.]+[0-9]/', $version, $matches)) {
            return $matches[0];
        }

        return $version;
    }

    /**
     * @return string|false
     */
    public function getPhpParam(string $name)
    {
        return ini_get($name);
    }

    /**
     * Whether a PHP extension is loaded.
     */
    public function hasPhpExtension(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * @deprecated Use `hasPhpExtension`.
     */
    public function hasPhpLib(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * Get a process PID.
     */
    public static function getPid(): ?int
    {
        if (!function_exists('getmypid')) {
            return null;
        }

        $pid = getmypid();

        if ($pid === false) {
            return null;
        }

        return $pid;
    }

    public static function isProcessActive(?int $pid): bool
    {
        if ($pid === null) {
            return false;
        }

        if (!self::isPosixSupported()) {
            return false;
        }

        if (posix_getsid($pid) === false) {
            return false;
        }

        return true;
    }

    public static function isPosixSupported(): bool
    {
        return function_exists('posix_getsid');
    }
}
