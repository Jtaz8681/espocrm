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

use Espo\Core\HttpClient\Exceptions\ConnectException;
use Espo\Core\HttpClient\Exceptions\NotAllowedInternalHost;
use Espo\Core\HttpClient\Exceptions\TooManyRedirectsException;
use Espo\Core\Utils\Security\UrlCheck;
use GuzzleHttp;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Client
{
    private const int MAX_REDIRECT_NUMBER = 5;

    /**
     * To be instantiated with the ClientFactory.
     *
     * @internal
     */
    public function __construct(
        private Options $options,
        private UrlCheck $urlCheck,
    ) {}

    /**
     * Send a request. Does not throw exceptions on error responses.
     *
     * @throws TooManyRedirectsException
     * @throws ConnectException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $client = $this->prepareGuzzleClient();

        try {
            return $client->send($request);
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            Util::handleConnectException($e);
        } catch (GuzzleHttp\Exception\TooManyRedirectsException $e) {
            throw new TooManyRedirectsException(previous: $e);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new RuntimeException(previous: $e);
        }
    }

    /**
     * Send a request in async.
     */
    public function sendAsync(RequestInterface $request): Promise
    {
        $client = $this->prepareGuzzleClient();

        $promise = $client->sendAsync($request);

        return new Promise($promise);
    }

    /**
     * @param string[] $allowed
     * @throws NotAllowedInternalHost
     */
    private function checkUrl(string $url, array $allowed): void
    {
        if (
            !Util::matchUrlToAddressList($url, $allowed) &&
            !$this->urlCheck->isUrlAndNotInternal($url)
        ) {
            throw new NotAllowedInternalHost("Not allowed internal host in '$url'.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareOptions(): array
    {
        $options = [
            'protocols' => array_map(
                fn (Protocol $protocol) => $protocol->value,
                $this->options->protocols
            ),
            'allow_redirects' => false,
            'http_errors' => false,
        ];

        if ($this->options->redirect->allow) {
            $options['allow_redirects'] = [
                'max' => $this->options->redirect->maxNumber ?? self::MAX_REDIRECT_NUMBER,
                'strict' => $this->options->redirect->strict,
                'protocols' => array_map(
                    fn (Protocol $protocol) => $protocol->value,
                    $this->options->redirect->protocols
                ),
            ];
        }

        if ($this->options->timeout !== null) {
            $options['timeout'] = $this->options->timeout;
        }

        if ($this->options->connectTimeout !== null) {
            $options['connect_timeout'] = $this->options->connectTimeout;
        }

        $stack = GuzzleHttp\HandlerStack::create(new GuzzleHttp\Handler\CurlHandler());

        if ($this->options->internalHostRestriction->restrict) {
            $stack->push(
                GuzzleHttp\Middleware::mapRequest(function (RequestInterface $request) {
                    $url = (string) $request->getUri();

                    $this->checkUrl($url, $this->options->internalHostRestriction->allowed);

                    return $request;
                })
            );

            $stack->push(function (callable $handler) {
                return function (RequestInterface $request, array $options) use ($handler) {
                    $url = (string) $request->getUri();

                    $resolve = $this->urlCheck->getCurlResolve($url);

                    if ($resolve === []) {
                        throw new NotAllowedInternalHost("Could not resolve host for '$url'.");
                    }

                    $allowed = $this->options->internalHostRestriction->allowed;

                    if ($resolve !== null && !$this->urlCheck->validateCurlResolveNotInternal($resolve, $allowed)) {
                        throw new NotAllowedInternalHost("Not allowed internal host in '$url'.");
                    }

                    if ($resolve) {
                        $options['curl'] ??= [];
                        $options['curl'][CURLOPT_RESOLVE] = $resolve;
                    }

                    return $handler($request, $options);
                };
            });
        }

        $options['handler'] = $stack;

        return $options;
    }

    private function prepareGuzzleClient(): GuzzleHttp\Client
    {
        $options = $this->prepareOptions();

        return new GuzzleHttp\Client($options);
    }
}
