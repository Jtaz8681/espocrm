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

use RuntimeException;

/**
 * An order item. Immutable.
 *
 * Immutable.
 */
class Order
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    private Expression $expression;
    private bool $isDesc = false;

    private function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Get an expression.
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    public function isDesc(): bool
    {
        return $this->isDesc;
    }

    /**
     * Get a direction.
     *
     * @return self::DESC|self::ASC
     */
    public function getDirection(): string
    {
        return $this->isDesc ? self::DESC : self::ASC;
    }

    /**
     * Create.
     */
    public static function create(Expression $expression): self
    {
        return new self($expression);
    }

    /**
     * Create from a string expression.
     */
    public static function fromString(string $expression): self
    {
        return self::create(
            Expression::create($expression)
        );
    }

    /**
     * Create an order by position in list.
     * Note: Reverses the list and applies DESC order.
     *
     * @param string[]|int[]|float[] $list
     */
    public static function createByPositionInList(Expression $expression, array $list): self
    {
        $orderExpression = Expression::positionInList($expression, array_reverse($list));

        return self::create($orderExpression)->withDesc();
    }

    /**
     * Clone with an ascending direction.
     */
    public function withAsc(): self
    {
        $obj = clone $this;
        $obj->isDesc = false;

        return $obj;
    }

    /**
     * Clone with a descending direction.
     */
    public function withDesc(): self
    {
        $obj = clone $this;
        $obj->isDesc = true;

        return $obj;
    }

    /**
     * Clone with a direction.
     *
     * @params self::ASC|self::DESC $direction
     * @throws RuntimeException
     */
    public function withDirection(string $direction): self
    {
        $obj = clone $this;
        $obj->isDesc = strtoupper($direction) === self::DESC;

        if (!in_array(strtoupper($direction), [self::DESC, self::ASC])) {
            throw new RuntimeException("Bad order direction.");
        }

        return $obj;
    }

    /**
     * Clone with a reverse direction.
     */
    public function withReverseDirection(): self
    {
        $obj = clone $this;
        $obj->isDesc = !$this->isDesc;

        return $obj;
    }
}
