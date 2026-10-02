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

namespace Espo\Core\Api;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @internal
 */
class MiddlewareProvider
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * @return MiddlewareInterface[]
     */
    public function getGlobalMiddlewareList(): array
    {
        return $this->createFromClassNameList($this->getGlobalMiddlewareClassNameList());
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getRouteMiddlewareList(Route $route): array
    {
        $key = strtolower($route->getMethod()) . '_' . $route->getRoute();

        /** @var class-string<MiddlewareInterface>[] $classNameList */
        $classNameList = $this->metadata->get(['app', 'api', 'routeMiddlewareClassNameListMap', $key]) ?? [];

        return $this->createFromClassNameList($classNameList);
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getActionMiddlewareList(Route $route): array
    {
        $key = strtolower($route->getMethod()) . '_' . $route->getRoute();

        /** @var class-string<MiddlewareInterface>[] $classNameList */
        $classNameList = $this->metadata->get(['app', 'api', 'actionMiddlewareClassNameListMap', $key]) ?? [];

        return $this->createFromClassNameList($classNameList);
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getControllerMiddlewareList(string $controller): array
    {
        /** @var class-string<MiddlewareInterface>[] $classNameList */
        $classNameList = $this->metadata
            ->get(['app', 'api', 'controllerMiddlewareClassNameListMap', $controller]) ?? [];

        return $this->createFromClassNameList($classNameList);
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getControllerActionMiddlewareList(string $method, string $controller, string $action): array
    {
        $key = $controller . '_' . strtolower($method) . '_' . $action;

        /** @var class-string<MiddlewareInterface>[] $classNameList */
        $classNameList = $this->metadata
            ->get(['app', 'api', 'controllerActionMiddlewareClassNameListMap', $key]) ?? [];

        return $this->createFromClassNameList($classNameList);
    }

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    private function getGlobalMiddlewareClassNameList(): array
    {
        return $this->metadata->get(['app', 'api', 'globalMiddlewareClassNameList']) ?? [];
    }

    /**
     * @param class-string<MiddlewareInterface>[] $classNameList
     * @return MiddlewareInterface[]
     */
    private function createFromClassNameList(array $classNameList): array
    {
        $list = [];

        foreach ($classNameList as $className) {
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }
}
