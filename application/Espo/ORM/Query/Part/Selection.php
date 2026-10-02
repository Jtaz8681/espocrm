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

/**
 * A select item. Immutable.
 *
 * Immutable.
 */
class Selection
{
    private function __construct(
        private Expression $expression,
        private ?string $alias = null
    ) {}

    public function getExpression(): Expression
    {
        return $this->expression;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public static function create(Expression $expression, ?string $alias = null): self
    {
        return new self($expression, $alias);
    }

    public static function fromString(string $expression): self
    {
        return self::create(
            Expression::create($expression)
        );
    }

    /**
     * With an alias. With null, the field name will be used as an alias or an expression itself.
     * Use `withNoAlias` to prevent alias addition.
     */
    public function withAlias(?string $alias): self
    {
        $obj = clone $this;
        $obj->alias = $alias;

        return $obj;
    }

    /**
     * With on alias.
     *
     * @since 9.3.0
     */
    public function withNoAlias(): self
    {
        $obj = clone $this;
        $obj->alias = '';

        return $obj;
    }
}
