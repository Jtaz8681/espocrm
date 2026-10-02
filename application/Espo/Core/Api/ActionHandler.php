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
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;

use Espo\Core\Utils\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * @internal
 */
class ActionHandler implements RequestHandlerInterface
{
    private const DEFAULT_CONTENT_TYPE = 'application/json';

    public function __construct(
        private Action $action,
        private ProcessData $processData,
        private Config $config
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     * @throws Conflict
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestWrapped = new RequestWrapper(
            $request,
            $this->processData->getBasePath(),
            $this->processData->getRouteParams()
        );

        $response = $this->action->process($requestWrapped);

        return $this->prepareResponse($response);
    }

    private function prepareResponse(Response $response): Psr7Response
    {
        if (!$response->hasHeader('Content-Type')) {
            $response->setHeader('Content-Type', self::DEFAULT_CONTENT_TYPE);
        }

        if (!$response->hasHeader('Cache-Control')) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        }

        if (!$response->hasHeader('Expires')) {
            $response->setHeader('Expires', '0');
        }

        if (!$response->hasHeader('Last-Modified')) {
            $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        }

        $response->setHeader('X-App-Timestamp', (string) ($this->config->get('appTimestamp') ?? '0'));

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $response instanceof ResponseWrapper ?
            $response->toPsr7() :
            self::responseToPsr7($response);
    }

    private static function responseToPsr7(Response $response): Psr7Response
    {
        $psr7Response = (new ResponseFactory())->createResponse();

        $statusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();

        $psr7Response = $psr7Response
            ->withStatus($statusCode, $reason)
            ->withBody($body);

        foreach ($response->getHeaderNames() as $name) {
            $psr7Response = $psr7Response->withHeader($name, $response->getHeaderAsArray($name));
        }

        return $psr7Response;
    }
}
