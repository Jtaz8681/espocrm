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

use Espo\Core\Api\Route\RouteParamsFetcher;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\Route as RouteUtil;
use Espo\Core\Utils\Log;

use Slim\App as SlimApp;
use Slim\Exception\HttpBadRequestException;
use Slim\Factory\AppFactory as SlimAppFactory;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Psr7Response;
use Psr\Http\Message\ServerRequestInterface as Psr7Request;

/**
 * API request processing entry point.
 */
class Starter
{
    private string $routeCacheFile = 'data/cache/application/slim-routes.php';

    public function __construct(
        private RouteProcessor $routeProcessor,
        private RouteUtil $routeUtil,
        private RouteParamsFetcher $routeParamsFetcher,
        private MiddlewareProvider $middlewareProvider,
        private Log $log,
        private SystemConfig $systemConfig,
        ?string $routeCacheFile = null
    ) {
        $this->routeCacheFile = $routeCacheFile ?? $this->routeCacheFile;
    }

    public function start(): void
    {
        $slim = SlimAppFactory::create();

        if (RouteUtil::isBadUri()) {
            $this->processError($slim);

            return;
        }

        if ($this->systemConfig->useCache()) {
            $slim->getRouteCollector()->setCacheFile($this->routeCacheFile);
        }

        $slim->setBasePath(RouteUtil::detectBasePath());
        $this->addGlobalMiddlewares($slim);
        $slim->addRoutingMiddleware();
        $this->addRoutes($slim);
        $slim->addErrorMiddleware(false, true, true, $this->log);

        $slim->run();
    }

    /**
     * @param SlimApp<ContainerInterface|null> $slim
     */
    private function addGlobalMiddlewares(SlimApp $slim): void
    {
        foreach ($this->middlewareProvider->getGlobalMiddlewareList() as $middleware) {
            $slim->add($middleware);
        }
    }

    /**
     * @param SlimApp<ContainerInterface|null> $slim
     */
    private function addRoutes(SlimApp $slim): void
    {
        $routeList = $this->routeUtil->getFullList();

        foreach ($routeList as $item) {
            $this->addRoute($slim, $item);
        }
    }

    /**
     * @param SlimApp<ContainerInterface|null> $slim
     */
    private function addRoute(SlimApp $slim, Route $item): void
    {
        $slimRoute = $slim->map(
            [$item->getMethod()],
            $item->getAdjustedRoute(),
            function (Psr7Request $request, Psr7Response $response, array $args) use ($slim, $item) {
                $routeParams = $this->routeParamsFetcher->fetch($item, $args);

                $processData = new ProcessData(
                    route: $item,
                    basePath: $slim->getBasePath(),
                    routeParams: $routeParams,
                );

                return $this->routeProcessor->process($processData, $request, $response);
            }
        );

        $middlewareList = $this->middlewareProvider->getRouteMiddlewareList($item);

        foreach ($middlewareList as $middleware) {
            $slimRoute->addMiddleware($middleware);
        }
    }

    /**
     * @param SlimApp<ContainerInterface|null> $slim
     */
    private function processError(SlimApp $slim): void
    {
        $slim->add(function (Psr7Request $request): Psr7Response {
            throw new HttpBadRequestException($request, 'Malformed request path.');
        });

        $slim->addErrorMiddleware(
            displayErrorDetails: false,
            logErrors: false,
            logErrorDetails: false,
        );

        $slim->run();
    }
}
