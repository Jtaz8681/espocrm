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

namespace Espo\ORM\Query\Part\Expression;

use Espo\ORM\Query\Part\Expression;

class Util
{
    /**
     * Compose an expression by a function name and arguments.
     *
     * @param Expression|bool|int|float|string|null ...$arguments Arguments
     */
    public static function composeFunction(
        string $function,
        Expression|bool|int|float|string|null ...$arguments
    ): Expression {

        $stringifiedItems = array_map(
            function ($item) {
                return self::stringifyArgument($item);
            },
            $arguments
        );

        $expression = $function . ':(' . implode(', ', $stringifiedItems) . ')';

        return Expression::create($expression);
    }

    /**
     * Stringify an argument.
     *
     * @param Expression|bool|int|float|string|null $argument
     */
    public static function stringifyArgument(Expression|bool|int|float|string|null $argument): string
    {

        if ($argument instanceof Expression) {
            return $argument->getValue();
        }

        if (is_null($argument)) {
            return 'NULL';
        }

       if (is_bool($argument)) {
            return $argument ? 'TRUE': 'FALSE';
        }

       if (is_int($argument)) {
           return strval($argument);
       }

       if (is_float($argument)) {
           return strval($argument);
       }

       return '\'' . str_replace('\'', '\\\'', $argument) . '\'';
    }
}
