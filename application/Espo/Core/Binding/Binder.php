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

use Espo\Core\Binding\Key\NamedClassKey;
use LogicException;
use Closure;

class Binder
{
    public function __construct(private BindingData $data)
    {}

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
        $this->validateBindingKey($key);

        $this->data->addGlobal(
            $key,
            Binding::createFromImplementationClassName($implementationClassName)
        );

        return $this;
    }

    /**
     * Bind an interface to a specific service.
     *
     * @param class-string<object>|NamedClassKey<object> $key An interface or interface with a parameter name.
     * @param string $serviceName A service name.
     */
    public function bindService(string|NamedClassKey $key, string $serviceName): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKey($key);

        $this->data->addGlobal(
            $key,
            Binding::createFromServiceName($serviceName)
        );

        return $this;
    }

    /**
     * Bind an interface to a callback.
     *
     * @template T of object
     * @param class-string<T>|NamedClassKey<T> $key An interface or interface with a parameter name.
     * @param Closure $callback A callback that will resolve a dependency.
     * @todo Change to Closure(...): T Once https://github.com/phpstan/phpstan/issues/8214 is implemented.
     */
    public function bindCallback(string|NamedClassKey $key, Closure $callback): self
    {
        $key = self::keyToString($key);
        $this->validateBindingKey($key);

        $this->data->addGlobal(
            $key,
            Binding::createFromCallback($callback)
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
        $this->validateBindingKey($key);

        $this->data->addGlobal(
            $key,
            Binding::createFromValue($instance)
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
        $this->validateBindingKey($key);

        $this->data->addGlobal(
            $key,
            Binding::createFromFactoryClassName($factoryClassName)
        );

        return $this;
    }

    /**
     * Creates a contextual binder and pass it as an argument of a callback.
     *
     * @param class-string<object> $className A context.
     * @param Closure(ContextualBinder): void $callback A callback with a `ContextualBinder` argument.
     */
    public function inContext(string $className, Closure $callback): self
    {
        $contextualBinder = new ContextualBinder($this->data, $className);

        $callback($contextualBinder);

        return $this;
    }

    /**
     * Creates a contextual binder.
     *
     * @param class-string<object> $className A context.
     */
    public function for(string $className): ContextualBinder
    {
        return new ContextualBinder($this->data, $className);
    }

    /**
     * @param string|NamedClassKey<object> $key
     */
    private static function keyToString(string|NamedClassKey $key): string
    {
        return is_string($key) ? $key : $key->toString();
    }

    private function validateBindingKey(string $key): void
    {
        if (!$key) {
            throw new LogicException("Bad binding.");
        }

        if ($key[0] === '$') {
            throw new LogicException("Can't binding a parameter name w/o an interface globally.");
        }
    }
}
