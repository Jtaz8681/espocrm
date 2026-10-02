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

namespace Espo\Core\Currency;

use DivisionByZeroError;

class CalculatorUtil
{
    private const int SCALE = 14;

    /**
     * @param numeric-string $arg1
     * @param numeric-string $arg2
     * @return numeric-string
     */
    public static function add(string $arg1, string $arg2): string
    {
        if (!function_exists('bcadd')) {
            return self::floatToString((float) $arg1 + (float) $arg2);
        }

        return bcadd($arg1, $arg2, self::SCALE);
    }

    /**
     * @param numeric-string $arg1
     * @param numeric-string $arg2
     * @return numeric-string
     */
    public static function subtract(string $arg1, string $arg2): string
    {
        if (!function_exists('bcsub')) {
            return self::floatToString((float) $arg1 - (float) $arg2);
        }

        return bcsub($arg1, $arg2, self::SCALE);
    }

    /**
     * @param numeric-string $arg1
     * @param numeric-string $arg2
     * @return numeric-string
     *
     * @todo For the result, trim right zeros. Then, trim dot.
     */
    public static function multiply(string $arg1, string $arg2): string
    {
        if (!function_exists('bcmul')) {
            return self::floatToString((float) $arg1 * (float) $arg2);
        }

        return bcmul($arg1, $arg2, self::SCALE);
    }

    /**
     * @param numeric-string $arg1
     * @param numeric-string $arg2
     * @return numeric-string
     *
     * @todo For the result, trim right zeros. Then, trim dot.
     */
    public static function divide(string $arg1, string $arg2): string
    {
        if (!function_exists('bcdiv')) {
            return self::floatToString((float) $arg1 / (float) $arg2);
        }

        $result = bcdiv($arg1, $arg2, self::SCALE);

        if ($result === null) { /** @phpstan-ignore-line  */
            throw new DivisionByZeroError();
        }

        return $result;
    }

    /**
     * @param numeric-string $arg
     * @return numeric-string
     */
    public static function round(string $arg, int $precision = 0): string
    {
        if (!function_exists('bcadd')) {
            return self::floatToString(
                round((float) $arg, $precision)
            );
        }

        $addition = '0.' . str_repeat('0', $precision) . '5';

        if ($arg[0] === '-') {
            $addition = '-' . $addition;
        }

        assert(is_numeric($addition));

        return bcadd($arg, $addition, $precision);
    }

    /**
     * @param numeric-string $arg1
     * @param numeric-string $arg2
     */
    public static function compare(string $arg1, string $arg2): int
    {
        if (!function_exists('bccomp')) {
            return (float) $arg1 <=> (float) $arg2;
        }

        return bccomp($arg1, $arg2, self::SCALE);
    }

    /**
     * @return numeric-string
     */
    private static function floatToString(float $amount): string
    {
        /** @var numeric-string */
        return rtrim(rtrim(sprintf('%.' . self::SCALE . 'f', $amount), '0'), '.');
    }
}
