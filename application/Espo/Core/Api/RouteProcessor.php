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

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Authentication\AuthenticationFactory;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Espo\Core\ApplicationUser;

use Psr\Http\Message\ResponseInterface as Psr7Response;
use Psr\Http\Message\ServerRequestInterface as Psr7Request;

use Slim\MiddlewareDispatcher;

use Throwable;
use LogicException;
use Exception;

/**
 * Processes routes. Handles authentication. Obtains a controller name, action, body from a request.
 * Then processes a controller action or an action.
 *
 * @internal
 */
class RouteProcessor
{
    public function __construct(
        private AuthenticationFactory $authenticationFactory,
        private AuthBuilderFactory $authBuilderFactory,
        private ErrorOutput $errorOutput,
        private Config $config,
        private Log $log,
        private ApplicationUser $applicationUser,
        private ControllerActionProcessor $actionProcessor,
        private MiddlewareProvider $middlewareProvider,
        private InjectableFactory $injectableFactory
    ) {}

    public function process(
        ProcessData $processData,
        Psr7Request $request,
        Psr7Response $response
    ): Psr7Response {

        $requestWrapped = new RequestWrapper($request, $processData->getBasePath(), $processData->getRouteParams());
        $responseWrapped = new ResponseWrapper($response);

        try {
            return $this->processInternal(
                $processData,
                $request,
                $requestWrapped,
                $responseWrapped
            );
        } catch (Exception $exception) {
            $this->handleException(
                $exception,
                $requestWrapped,
                $responseWrapped,
                $processData->getRoute()->getAdjustedRoute()
            );

            return $responseWrapped->toPsr7();
        }
    }

    /**
     * @throws BadRequest
     */
    private function processInternal(
        ProcessData $processData,
        Psr7Request $psrRequest,
        RequestWrapper $request,
        ResponseWrapper $response
    ): Psr7Response {

        $authRequired = !$processData->getRoute()->noAuth();

        $apiAuth = $this->authBuilderFactory
            ->create()
            ->setAuthentication($this->authenticationFactory->create())
            ->setAuthRequired($authRequired)
            ->build();

        $authResult = $apiAuth->process($request, $response);

        if (!$authResult->isResolved()) {
            return $response->toPsr7();
        }

        if ($authResult->isResolvedUseNoAuth()) {
            $this->applicationUser->setupSystemUser();
        }

        ob_start();

        $response = $this->processAfterAuth($processData, $psrRequest, $response);

        ob_clean();

        return $response;
    }

    /**
     * @throws BadRequest
     */
    private function processAfterAuth(
        ProcessData $processData,
        Psr7Request $request,
        ResponseWrapper $responseWrapped
    ): Psr7Response {

        $actionClassName = $processData->getRoute()->getActionClassName();

        if ($actionClassName) {
            return $this->processAction($actionClassName, $processData, $request, $responseWrapped);
        }

        return $this->processControllerAction($processData, $request, $responseWrapped);
    }

    /**
     * @param class-string<Action> $actionClassName
     */
    private function processAction(
        string $actionClassName,
        ProcessData $processData,
        Psr7Request $request,
        ResponseWrapper $responseWrapped
    ): Psr7Response {

        /** @var Action $action */
        $action = $this->injectableFactory->create($actionClassName);

        $handler = new ActionHandler(
            action: $action,
            processData: $processData,
            config: $this->config,
        );

        $dispatcher = new MiddlewareDispatcher($handler);

        foreach ($this->middlewareProvider->getActionMiddlewareList($processData->getRoute()) as $middleware) {
            $dispatcher->addMiddleware($middleware);
        }

        $response = $dispatcher->handle($request);

        // Apply headers added by the authentication.
        foreach ($responseWrapped->getHeaderNames() as $name) {
            $response = $response->withHeader($name, $responseWrapped->getHeaderAsArray($name));
        }

        return $response;
    }

    /**
     * @throws BadRequest
     */
    private function processControllerAction(
        ProcessData $processData,
        Psr7Request $request,
        ResponseWrapper $responseWrapped
    ): Psr7Response {

        $controller = $this->getControllerName($processData);
        $action = $processData->getRouteParams()['action'] ?? null;
        $method = $request->getMethod();

        if (!$action) {
            $crudMethodActionMap = $this->config->get('crud') ?? [];
            $action = $crudMethodActionMap[strtolower($method)] ?? null;

            if (!$action) {
                throw new BadRequest("No action for method `$method`.");
            }
        }

        $handler = new ControllerActionHandler(
            controllerName: $controller,
            actionName: $action,
            processData: $processData,
            responseWrapped: $responseWrapped,
            controllerActionProcessor: $this->actionProcessor,
            config: $this->config,
        );

        $dispatcher = new MiddlewareDispatcher($handler);

        $this->addControllerMiddlewares($dispatcher, $method, $controller, $action);

        return $dispatcher->handle($request);
    }

    private function getControllerName(ProcessData $processData): string
    {
        $controllerName = $processData->getRouteParams()['controller'] ?? null;

        if (!$controllerName) {
            throw new LogicException("Route doesn't have specified controller.");
        }

        return ucfirst($controllerName);
    }

    private function handleException(
        Exception $exception,
        Request $request,
        Response $response,
        string $route
    ): void {

        try {
            $this->errorOutput->process($request, $response, $exception, $route);
        } catch (Throwable $exceptionAnother) {
            $this->log->error($exceptionAnother->getMessage());

            $response->setStatus(500);
        }
    }

    /**
     * @param MiddlewareDispatcher<null> $dispatcher
     */
    private function addControllerMiddlewares(
        MiddlewareDispatcher $dispatcher,
        string $method,
        string $controller,
        string $action
    ): void {

        $controllerActionMiddlewareList = $this->middlewareProvider
            ->getControllerActionMiddlewareList($method, $controller, $action);

        foreach ($controllerActionMiddlewareList as $middleware) {
            $dispatcher->addMiddleware($middleware);
        }

        $controllerMiddlewareList = $this->middlewareProvider
            ->getControllerMiddlewareList($controller);

        foreach ($controllerMiddlewareList as $middleware) {
            $dispatcher->addMiddleware($middleware);
        }
    }
}
