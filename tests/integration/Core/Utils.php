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

namespace tests\integration\Core;

use RuntimeException;

class Utils
{
    /**
     * Get the latest EspoCRM built path.
     *
     * @param string $path
     * @return ?string
     */
    public static function getLatestBuiltPath($path): ?string
    {
        $archives = [];

        $buildDir = dir($path);

        if (!$buildDir) {
            throw new RuntimeException("No test build. Run `grunt test`.");
        }

        while ($folderName = $buildDir->read()) {
            if ($folderName === '.'|| $folderName === '..' || empty($folderName)) {
                continue;
            }

            $pattern = '/^EspoCRM-([0-9]+)\.([0-9]+)(?:\.([0-9]+))?(?:-((a|alpha|b|beta|pre|rc)([0-9]+)?)?)?$/';

            if (preg_match($pattern, $folderName)) {
                $archives[] = $folderName;
            }
        }

        if (count($archives) > 0) {
            static::sortVersions($archives);

            return $path . '/' . $archives[count($archives) - 1];
        }

        return null;
    }

    private static function sortVersions(&$existVersions)
    {
        usort($existVersions, ["\\tests\\integration\\Core\\Utils", "versionCmp"]);
    }

    public static function versionCmp($a, $b): int
    {
        $order = ['a' => 0, 'alpha' => 1, 'b' => 2, 'beta' => 3, 'pre' => 4, 'rc' => 5];

        $ma = $mb = [];

        $pattern = '/^EspoCRM-([0-9]+)\.([0-9]+)(?:\.([0-9]+))?(?:-((a|alpha|b|beta|pre|rc)[0-9]+)?)?$/';

        preg_match($pattern, $a, $ma);
        preg_match($pattern, $b, $mb);

        if ($ma[1] != $mb[1]) {
            return (int) $ma[1] < (int) $mb[1] ? -1 : 1;
        }

        if ($ma[2] != $mb[2]) {
            return (int) $ma[2] < (int) $mb[2] ? -1 : 1;
        }

        if (!isset($ma[3])) {
            $ma[3] = 0;
        }

        if (!isset($mb[3])) {
            $mb[3] = 0;
        }

        if ($ma[3] != $mb[3]) {
            return (int) $ma[3] < (int) $mb[3] ? -1 : 1;
        }

        if (isset($ma[4]) && !isset($mb[4])) {
            return -1;
        }

        if (!isset($ma[4]) && isset($mb[4])) {
            return 1;
        }

        if (@$ma[5] != @$mb[5]) {
            return ($order[$ma[5]] < $order[$mb[5]]) ? -1 : 1;
        }

        if (@$ma[4] != @$mb[4]) {
            return ($ma[4] < $mb[4]) ? -1 : 1;
        }

        return 0;
    }

    public static function fixUndefinedVariables(): void
    {
        $list = [
            'REQUEST_METHOD',
            'REMOTE_ADDR',
            'SERVER_NAME',
            'SERVER_PORT',
            'REQUEST_URI',
            'HTTPS',
        ];

        foreach ($list as $name) {
            if (!array_key_exists($name, $_SERVER)) {
                $_SERVER[$name] = '';
            }
        }
    }
}
