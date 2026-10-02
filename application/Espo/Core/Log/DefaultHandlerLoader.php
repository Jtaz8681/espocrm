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

namespace Espo\Core\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

use ReflectionClass;
use RuntimeException;

/**
 * @internal
 *
 * @phpstan-type DefaultHandlerLoaderData array{
 *     className?: ?class-string<HandlerInterface>,
 *     params?: ?array<string, mixed>,
 *     level?: string|null,
 *     formatter?: ?FormatterData,
 * }
 * @phpstan-type FormatterData array{
 *     className?: ?class-string<FormatterInterface>,
 *     params?: ?array<string, mixed>,
 * }
 */
class DefaultHandlerLoader
{
    /**
     * @param DefaultHandlerLoaderData $data
     */
    public function load(array $data, ?string $defaultLevel = null): HandlerInterface
    {
        $params = $data['params'] ?? [];
        $level = $data['level'] ?? $defaultLevel;

        if ($level) {
            /** @phpstan-ignore-next-line */
            $params['level'] = Logger::toMonologLevel($level);
        }

        $className = $data['className'] ?? null;

        if (!$className) {
            throw new RuntimeException("Log handler does not have className specified.");
        }

        $handler = $this->createInstance($className, $params);

        $formatter = $this->loadFormatter($data);

        if ($formatter && $handler instanceof FormattableHandlerInterface) {
            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    /**
     * @internal
     * @param DefaultHandlerLoaderData $data
     */
    public function loadFormatter(array $data): ?FormatterInterface
    {
        $formatterData = $data['formatter'] ?? null;

        if (!$formatterData || !is_array($formatterData)) {
            return null;
        }

        $className = $formatterData['className'] ?? null;

        if (!$className) {
            return null;
        }

        $params = $formatterData['params'] ?? [];

        return $this->createInstance($className, $params);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $params
     * @return T
     */
    private function createInstance(string $className, array $params): object
    {
        $class = new ReflectionClass($className);

        $constructor = $class->getConstructor();

        if (!$constructor) {
            return $class->newInstanceArgs([]);
        }

        $argumentList = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $params)) {
                $value = $params[$name];
            } else if ($parameter->isDefaultValueAvailable()) {
                $value = $parameter->getDefaultValue();
            } else {
                continue;
            }

            $argumentList[] = $value;
        }

        return $class->newInstanceArgs($argumentList);
    }
}
