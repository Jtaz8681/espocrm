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

namespace Espo\Core\Upgrades\Migration;

use RuntimeException;

class VersionUtil
{
    /**
     * @param string[] $fullList
     * @return string[]
     */
    public static function extractSteps(string $from, string $to, array $fullList): array
    {
        usort($fullList, fn ($v1, $v2) => version_compare($v1, $v2));

        $isPatch = self::isPatch($from, $to);

        $list = [];
        $nextMinorIsPassed = false;

        foreach ($fullList as $item) {
            $a = self::split($item);

            if ($isPatch && $a[2] === null) {
                continue;
            }

            $v1 = $a[0] . '.' . $a[1] . '.' . ($a[2] ?? '0');

            if (version_compare($v1, $from) <= 0) {
                continue;
            }

            if (version_compare($v1, $to) > 0) {
                continue;
            }

            $isItemPatch = $a[2] !== null;

            if (!$isPatch && $isItemPatch && $nextMinorIsPassed) {
                continue;
            }

            if (!$nextMinorIsPassed && !self::isPatch($from, $v1)) {
                $nextMinorIsPassed = true;
            }

            $list[] = $item;
        }

        return $list;
    }

    /**
     * @return array{string, string, ?string}
     */
    public static function split(string $version): array
    {
        $array = explode('.', $version, 3);

        if (count($array) === 2) {
            return [$array[0], $array[1], null];
        }

        if (count($array) !== 3) {
            throw new RuntimeException("Bad version number $version.");
        }

        /** @var array{string, string, string} */
        return $array;
    }

    public static function isPatch(string $from, string $to): bool
    {
        $aFrom = self::split($from);
        $aTo = self::split($to);

        return $aFrom[0] === $aTo[0] && $aFrom[1] === $aTo[1];
    }

    public static function stepToVersion(string $step): string
    {
        $a = self::split($step);

        if ($a[2] === null) {
            $a[2] = '0';
        }

        return implode('.', $a);
    }
}
