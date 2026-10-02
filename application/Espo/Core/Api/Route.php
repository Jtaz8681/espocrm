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

class Route
{
    private string $method;

    /**
     * @param array<string, string> $params
     * @param ?class-string<Action> $actionClassName
     */
    public function __construct(
        string $method,
        private string $route,
        private string $adjustedRoute,
        private array $params,
        private bool $noAuth,
        private ?string $actionClassName
    ) {
        $this->method = strtoupper($method);
    }

    /**
     * @return ?class-string<Action>
     */
    public function getActionClassName(): ?string
    {
        return $this->actionClassName;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get a route.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get an adjusted route for FastRoute.
     */
    public function getAdjustedRoute(): string
    {
        return $this->adjustedRoute;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function noAuth(): bool
    {
        return $this->noAuth;
    }
}
