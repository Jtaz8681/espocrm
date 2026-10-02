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

namespace Espo\ORM\Query\Part;

use Espo\ORM\Query\Part\Where\AndGroup;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Where\Exists;
use Espo\ORM\Query\Part\Where\Not;
use Espo\ORM\Query\Part\Where\OrGroup;

use Espo\ORM\Query\Select;

/**
 * A util-class for creating items that can be used as a where-clause.
 */
class Condition
{
    private function __construct()
    {}

    /**
     * Create 'AND' group.
     */
    public static function and(WhereItem ...$items): AndGroup
    {
        return AndGroup::create(...$items);
    }

    /**
     * Create 'OR' group.
     */
    public static function or(WhereItem ...$items): OrGroup
    {
        return OrGroup::create(...$items);
    }

    /**
     * Create 'NOT'.
     */
    public static function not(WhereItem $item): Not
    {
        return Not::create($item);
    }

    /**
     * Create `EXISTS`.
     */
    public static function exists(Select $subQuery): Exists
    {
        return Exists::create($subQuery);
    }

    /**
     * Create a column reference expression.
     *
     * @param string $expression Examples: `columnName`, `alias.columnName`.
     */
    public static function column(string $expression): Expression
    {
        return Expression::column($expression);
    }

    /**
     * Create '=' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float|bool|null $argument2 A scalar, expression or sub-query.
     */
    public static function equal(
        Expression $argument1,
        Expression|Select|string|int|float|bool|null $argument2
    ): Comparison {

        return Comparison::equal($argument1, $argument2);
    }

    /**
     * Create '!=' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float|bool|null $argument2 A scalar, expression or sub-query.
     */
    public static function notEqual(
        Expression $argument1,
        Expression|Select|string|int|float|bool|null $argument2
    ): Comparison {

        return Comparison::notEqual($argument1, $argument2);
    }

    /**
     * Create 'LIKE' comparison.
     *
     * @param Expression $subject What to test.
     * @param Expression|string $pattern A pattern.
     */
    public static function like(Expression $subject, Expression|string $pattern): Comparison
    {
        return Comparison::like($subject, $pattern);
    }

    /**
     * Create 'NOT LIKE' comparison.
     *
     * @param Expression $subject What to test.
     * @param Expression|string $pattern A pattern.
     */
    public static function notLike(Expression $subject, Expression|string $pattern): Comparison
    {
        return Comparison::notLike($subject, $pattern);
    }

    /**
     * Create '>' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float $argument2 A scalar, expression or sub-query.
     */
    public static function greater(
        Expression $argument1,
        Expression|Select|string|int|float $argument2
    ): Comparison {

        return Comparison::greater($argument1, $argument2);
    }

    /**
     * Create '>=' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float $argument2 A scalar, expression or sub-query.
     */
    public static function greaterOrEqual(
        Expression $argument1,
        Expression|Select|string|int|float $argument2
    ): Comparison {

        return Comparison::greaterOrEqual($argument1, $argument2);
    }

    /**
     * Create '<' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float $argument2 A scalar, expression or sub-query.
     */
    public static function less(
        Expression $argument1,
        Expression|Select|string|int|float $argument2
    ): Comparison {

        return Comparison::less($argument1, $argument2);
    }

    /**
     * Create '<=' comparison.
     *
     * @param Expression $argument1 An expression.
     * @param Expression|Select|string|int|float $argument2 A scalar, expression or sub-query.
     */
    public static function lessOrEqual(
        Expression $argument1,
        Expression|Select|string|int|float $argument2
    ): Comparison {

        return Comparison::lessOrEqual($argument1, $argument2);
    }

    /**
     * Create 'IN' comparison.
     *
     * @param Expression $subject What to test.
     * @param Select|scalar[] $set A set of values. A select query or array of scalars.
     */
    public static function in(Expression $subject, Select|array $set): Comparison
    {
        return Comparison::in($subject, $set);
    }

    /**
     * Create 'NOT IN' comparison.
     *
     * @param Expression $subject What to test.
     * @param Select|scalar[] $set A set of values. A select query or array of scalars.
     */
    public static function notIn(Expression $subject, Select|array $set): Comparison
    {
        return Comparison::notIn($subject, $set);
    }
}
