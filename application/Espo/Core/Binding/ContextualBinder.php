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

use Closure;
use Espo\Core\Binding\Key\NamedClassKey;
use Espo\Core\Binding\Key\NamedKey;
use LogicException;

class ContextualBinder
{
    private BindingData $data;
    /** @var class-string<object> */
    private string $className;

    /**
     * @param class-string<object> $className
     */
    public function __construct(BindingData $data, string $className)
    {
        $this->data = $data;
        $this->className = $className;
    }

    /**
     * Bind an interface to an implementation.
     *
     * @template T of object
     * @param class-string<T>|NamedClassKey<T> $key An interface or interface with a parameter name.
     * @param class-string<T> $implementationClassName An implementation class name.
     */
    public function bindImplementation(string|NamedClassKey $key, string $implementationClassName): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKeyNoParameterName($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromImplementationClassName($implementationClassName)
        );

        return $this;
    }

    /**
     * Bind an interface to a specific service.
     *
     * @template T of object
     * @param class-string<T>|NamedClassKey<T> $key An interface or interface with a parameter name.
     * @param string $serviceName A service name.
     */
    public function bindService(string|NamedClassKey $key, string $serviceName): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKeyNoParameterName($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromServiceName($serviceName)
        );

        return $this;
    }

    /**
     * Bind an interface or parameter name to a specific value.
     *
     * @param string|NamedKey|NamedClassKey<object> $key Parameter name (`$name`) or interface with a parameter name.
     * @param mixed $value A value of any type.
     */
    public function bindValue(string|NamedKey|NamedClassKey $key, $value): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKeyParameterName($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromValue($value)
        );

        return $this;
    }

    /**
     * Bind an interface to a specific instance.
     *
     * @template T of object
     * @param class-string<T>|NamedClassKey<T> $key An interface or interface with a parameter name.
     * @param T $instance An instance.
     * @noinspection PhpDocSignatureInspection
     */
    public function bindInstance(string|NamedClassKey $key, object $instance): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKeyNoParameterName($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromValue($instance)
        );

        return $this;
    }

    /**
     * Bind an interface or parameter name to a callback.
     *
     * @param class-string<object>|NamedClassKey<object>|NamedKey $key An interface, parameter name or both.
     * @param Closure $callback A callback that will resolve a dependency.
     * @todo Change to Closure(...): mixed Once https://github.com/phpstan/phpstan/issues/8214 is implemented.
     */
    public function bindCallback(string|NamedClassKey|NamedKey $key, Closure $callback): self
    {
        $key = self::keyToString($key);
        $this->validateBinding($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromCallback($callback)
        );

        return $this;
    }

    /**
     * Bind an interface to a factory.
     *
     * @template T of object
     * @param class-string<T>|NamedClassKey<T> $key An interface or interface with a parameter name.
     * @param class-string<Factory<T>> $factoryClassName A factory class name.
     */
    public function bindFactory(string|NamedClassKey $key, string $factoryClassName): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKeyNoParameterName($key);

        $this->data->addContext(
            $this->className,
            $key,
            Binding::createFromFactoryClassName($factoryClassName)
        );

        return $this;
    }

    private function validateBinding(string $key): void
    {
        if (!$key) {
            throw new LogicException("Bad binding.");
        }
    }

    private function validateBindingKeyNoParameterName(string $key): void
    {
        $this->validateBinding($key);

        if ($key[0] === '$') {
            throw new LogicException("Can't bind a parameter name w/o an interface.");
        }
    }

    private function validateBindingKeyParameterName(string $key): void
    {
        $this->validateBinding($key);

        if (!str_contains($key, '$')) {
            throw new LogicException("Can't bind w/o a parameter name.");
        }
    }

    /**
     * @param string|NamedKey|NamedClassKey<object> $key
     */
    private static function keyToString(string|NamedKey|NamedClassKey $key): string
    {
        return is_string($key) ? $key : $key->toString();
    }
}
