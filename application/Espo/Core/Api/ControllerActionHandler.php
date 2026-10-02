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

use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
class ControllerActionHandler implements RequestHandlerInterface
{
    public function __construct(
        private string $controllerName,
        private string $actionName,
        private ProcessData $processData,
        private ResponseWrapper $responseWrapped,
        private ControllerActionProcessor $controllerActionProcessor,
        private Config $config
    ) {}

    /**
     * @throws NotFound
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestWrapped = new RequestWrapper(
            $request,
            $this->processData->getBasePath(),
            $this->processData->getRouteParams()
        );

        $this->beforeProceed();

        $responseWrapped = $this->controllerActionProcessor->process(
            $this->controllerName,
            $this->actionName,
            $requestWrapped,
            $this->responseWrapped
        );

        $this->afterProceed($responseWrapped);

        return $responseWrapped->toPsr7();
    }

    private function beforeProceed(): void
    {
        $this->responseWrapped->setHeader('Content-Type', 'application/json');
    }

    private function afterProceed(Response $responseWrapped): void
    {
        $responseWrapped
            ->setHeader('X-App-Timestamp', (string) ($this->config->get('appTimestamp') ?? '0'))
            ->setHeader('Expires', '0')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
}
