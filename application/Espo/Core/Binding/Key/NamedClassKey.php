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

namespace Espo\Core\Binding\Key;

/**
 * A key for a class-type-hinted constructor parameter with a parameter name.
 *
 * @template-covariant T of object
 */
class NamedClassKey
{
    /**
     * @param class-string<T> $className
     */
    private function __construct(private string $className, private string $parameterName)
    {}

    /**
     * Create.
     *
     * @template TC of object
     * @param class-string<TC> $className An interface.
     * @param string $parameterName A constructor parameter name (w/o '$').
     * @return self<TC>
     */
    public static function create(string $className, string $parameterName): self
    {
        return new self($className, $parameterName);
    }

    public function toString(): string
    {
        return $this->className . ' $' . $this->parameterName;
    }
}
