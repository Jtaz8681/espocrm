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

namespace Espo\Core\Container;

use Espo\Core\Container\Exceptions\NotSettableException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

/**
 * DI container for services. Lazy initialization is used. Services are instantiated only once.
 * @see #
 */
interface Container extends ContainerInterface
{
    /**
     * Obtain a service object.
     *
     * @throws NotFoundExceptionInterface If not gettable.
     */
    public function get(string $id): object;

    /**
     * Check whether a service can be obtained.
     */
    public function has(string $id): bool;

    /**
     * Set a service object. Must be configured as settable.
     *
     * @throws NotSettableException Is not settable or already set.
     */
    public function set(string $id, object $object): void;

    /**
     * Get a class of a service.
     *
     * @return ReflectionClass<object>
     * @throws NotFoundExceptionInterface If not gettable.
     */
    public function getClass(string $id): ReflectionClass;

    /**
     * Get a service by a class name. A service should be bound to a class or interface.
     *
     * @template T of object
     * @param class-string<T> $className A class name or interface name.
     * @return T A service instance.
     * @throws NotFoundExceptionInterface If not gettable.
     */
    public function getByClass(string $className): object;
}
