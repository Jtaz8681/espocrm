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

namespace tests\unit\Espo\Core\Api;

use Espo\Core\Api\Auth;
use Espo\Core\Api\RequestWrapper;
use Espo\Core\Api\Response;
use Espo\Core\Authentication\Authentication;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\Core\Authentication\HeaderKey;
use Espo\Core\Authentication\Result;
use Espo\Core\Authentication\Result\Data;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Log;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class AuthTest extends TestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testProcessNoAuth(): void
    {
        $request = $this->createRequestInstance();

        $response = $this->createMock(Response::class);

        $authentication = $this->createMock(Authentication::class);
        $authentication
            ->expects(self::once())
            ->method('login')
            ->willReturn(
                Result::secondStepRequired($this->createMock(User::class), Data::create())
            );

        $configDataProvider = $this->createConfigDataProvider();

        $auth = new Auth(
            log: $this->createMock(Log::class),
            authentication: $authentication,
            configDataProvider: $configDataProvider,
            authRequired: false,
        );

        $result = $auth->process($request, $response);

        $this->assertTrue($result->isResolvedUseNoAuth());
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testProcessAuthFail(): void
    {
        $request = $this->createRequestInstance();

        $response = $this->createMock(Response::class);

        $authentication = $this->createMock(Authentication::class);
        $authentication
            ->expects(self::once())
            ->method('login')
            ->willReturn(
                Result::fail()
            );

        $configDataProvider = $this->createConfigDataProvider();

        $auth = new Auth(
            log: $this->createMock(Log::class),
            authentication: $authentication,
            configDataProvider: $configDataProvider,
        );

        $result = $auth->process($request, $response);

        $this->assertFalse($result->isResolved());
        $this->assertFalse($result->isResolvedUseNoAuth());
    }

    private function createRequest(
        string $method,
        array $queryParams = [],
        array $headers = [],
        ?string $body = null,
    ): RequestWrapper {

        $request = (new RequestFactory())
            ->createRequest($method, 'http://localhost/?' . http_build_query($queryParams));

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body) {
            $request = $request->withBody(
                (new StreamFactory)->createStream($body)
            );
        }

        return new RequestWrapper($request, '', []);
    }

    private function createRequestInstance(): RequestWrapper
    {
        return $this->createRequest(
            method: 'POST',
            headers: [
                'Content-Type' => 'application/json',
                HeaderKey::AUTHORIZATION => base64_encode('test:1'),
            ],
            body: Json::encode((object) []),
        );
    }

    private function createConfigDataProvider(): ConfigDataProvider
    {
        $configDataProvider = $this->createMock(ConfigDataProvider::class);
        $configDataProvider
            ->method('getLoginMetadataParamsList')
            ->willReturn([]);

        return $configDataProvider;
    }
}
