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

namespace Espo\Core\HttpClient;

use Closure;
use Espo\Core\HttpClient\Exceptions\ConnectException;
use Espo\Core\HttpClient\Exceptions\TooManyRedirectsException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use UnexpectedValueException;

class Promise
{
    /**
     * @internal
     */
    public function __construct(
        private PromiseInterface $promise,
    ) {}

    /**
     * @throws TooManyRedirectsException
     * @throws ConnectException
     * @internal
     */
    public function wait(): ResponseInterface
    {
        $value = $this->requestCallback(fn () => $this->promise->wait());

        if (!$value instanceof ResponseInterface) {
            throw new UnexpectedValueException();
        }

        return $value;
    }

    /**
     * @throws TooManyRedirectsException
     * @throws ConnectException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    private function requestCallback(Closure $closure): mixed
    {
        try {
            return $closure();
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            Util::handleConnectException($e);
        } catch (GuzzleHttp\Exception\TooManyRedirectsException $e) {
            throw new TooManyRedirectsException(previous: $e);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new RuntimeException(previous: $e);
        }
    }
}
