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

namespace Espo\Core\Binding;

use LogicException;

class Binding
{
    public const IMPLEMENTATION_CLASS_NAME = 1;
    public const CONTAINER_SERVICE = 2;
    public const VALUE = 3;
    public const CALLBACK = 4;
    public const FACTORY_CLASS_NAME = 5;

    private int $type;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    private function __construct(int $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param class-string<object> $implementationClassName
     */
    public static function createFromImplementationClassName(string $implementationClassName): self
    {
        if (!$implementationClassName) {
            throw new LogicException("Bad binding.");
        }

        return new self(self::IMPLEMENTATION_CLASS_NAME, $implementationClassName);
    }

    public static function createFromServiceName(string $serviceName): self
    {
        if (!$serviceName) {
            throw new LogicException("Bad binding.");
        }

        return new self(self::CONTAINER_SERVICE, $serviceName);
    }

    /**
     * @param mixed $value
     */
    public static function createFromValue($value): self
    {
        return new self(self::VALUE, $value);
    }

    public static function createFromCallback(callable $callback): self
    {
        return new self(self::CALLBACK, $callback);
    }

    /**
     * @param class-string<Factory<object>> $factoryClassName
     */
    public static function createFromFactoryClassName(string $factoryClassName): self
    {
        if (!$factoryClassName) {
            throw new LogicException("Bad binding.");
        }

        return new self(self::FACTORY_CLASS_NAME, $factoryClassName);
    }
}
