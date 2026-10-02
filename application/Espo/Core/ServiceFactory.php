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

namespace Espo\Core;

use Espo\Core\Utils\ClassFinder;

use RuntimeException;

/**
 * @deprecated As of v6.1. For business logic, use plain classes. Inject them via constructor.
 * To access record services of specific entity types use `Espo\Core\Record\ServiceContainer`.
 */
class ServiceFactory
{
    private $classFinder;
    private $injectableFactory;

    public function __construct(ClassFinder $classFinder, InjectableFactory $injectableFactory)
    {
        $this->classFinder = $classFinder;
        $this->injectableFactory = $injectableFactory;
    }

    /**
     * @return ?class-string
     */
    private function getClassName(string $name): ?string
    {
        return $this->classFinder->find('Services', $name);
    }

    public function checkExists(string $name): bool
    {
        $className = $this->getClassName($name);

        if (!$className) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $with
     */
    public function createWith(string $name, array $with): object
    {
        $className = $this->getClassName($name);

        if (!$className) {
            throw new RuntimeException("Service '{$name}' was not found.");
        }

        $obj = $this->injectableFactory->createWith($className, $with);

        // For backward compatibility.
        if (method_exists($obj, 'prepare')) {
            $obj->prepare();
        }

        return $obj;
    }

    public function create(string $name): object
    {
        return $this->createWith($name, []);
    }
}
