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

use ReflectionClass;
use ReflectionParameter;
use ReflectionNamedType;
use LogicException;

/**
 * Access point for bindings.
 */
class BindingContainer
{
    public function __construct(private BindingData $data)
    {}

    /**
     * Has binding by a reflection parameter.
     *
     * @param ?ReflectionClass<object> $class
     */
    public function hasByParam(?ReflectionClass $class, ReflectionParameter $param): bool
    {
        if ($this->getInternal($class, $param) === null) {
            return false;
        }

        return true;
    }

    /**
     * Get binding by a reflection parameter.
     *
     * @param ?ReflectionClass<object> $class
     */
    public function getByParam(?ReflectionClass $class, ReflectionParameter $param): Binding
    {
        if (!$this->hasByParam($class, $param)) {
            throw new LogicException("Cannot get not existing binding.");
        }

        /** @var Binding */
        return $this->getInternal($class, $param);
    }

    /**
     * Has global binding by an interface.
     *
     * @param class-string $interfaceName
     */
    public function hasByInterface(string $interfaceName): bool
    {
        return $this->data->hasGlobal($interfaceName);
    }

    /**
     * Get global binding by an interface.
     *
     * @param class-string $interfaceName
     */
    public function getByInterface(string $interfaceName): Binding
    {
        if (!$this->hasByInterface($interfaceName)) {
            throw new LogicException("Binding for interface `$interfaceName` does not exist.");
        }

        if (!interface_exists($interfaceName) && !class_exists($interfaceName)) {
            throw new LogicException("Interface `$interfaceName` does not exist.");
        }

        return $this->data->getGlobal($interfaceName);
    }

    /**
     * @param ?ReflectionClass<object> $class
     */
    private function getInternal(?ReflectionClass $class, ReflectionParameter $param): ?Binding
    {
        $className = null;

        $key = null;

        if ($class) {
            $className = $class->getName();

            $key = '$' . $param->getName();
        }

        $type = $param->getType();

        if (
            $className &&
            $key &&
            $this->data->hasContext($className, $key)
        ) {
            $binding = $this->data->getContext($className, $key);

            $notMatching =
                $type instanceof ReflectionNamedType &&
                !$type->isBuiltin() &&
                $binding->getType() === Binding::VALUE &&
                is_scalar($binding->getValue());

            if (!$notMatching) {
                return $binding;
            }
        }

        $dependencyClassName = null;

        if (
            $type instanceof ReflectionNamedType &&
            !$type->isBuiltin()
        ) {
            $dependencyClassName = $type->getName();
        }

        $key = null;
        $keyWithParamName = null;

        if ($dependencyClassName) {
            $key = $dependencyClassName;

            $keyWithParamName = $key . ' $' . $param->getName();
        }

        if ($keyWithParamName) {
            if ($className && $this->data->hasContext($className, $keyWithParamName)) {
                return $this->data->getContext($className, $keyWithParamName);
            }

            if ($this->data->hasGlobal($keyWithParamName)) {
                return $this->data->getGlobal($keyWithParamName);
            }
        }

        if ($key) {
            if ($className && $this->data->hasContext($className, $key)) {
                return $this->data->getContext($className, $key);
            }

            if ($this->data->hasGlobal($key)) {
                return $this->data->getGlobal($key);
            }
        }

        return null;
    }
}
