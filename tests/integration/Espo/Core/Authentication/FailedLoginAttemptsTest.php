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

namespace tests\integration\Espo\Core\Authentication;

use Espo\Core\Api\RequestWrapper;
use Espo\Core\Api\Response;
use Espo\Core\Authentication\Authentication;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Authentication\HeaderKey;
use Espo\Core\Authentication\Util\DelayUtil;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingProcessor;
use Espo\Core\Utils\Config\ConfigWriter;
use Slim\Psr7\Factory\ServerRequestFactory;
use tests\integration\Core\BaseTestCase;

class FailedLoginAttemptsTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testUsernameFailedLogin(): void
    {
        $delay = 5;

        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);
        $configWriter->setMultiple([
            'authUsernameFailedAttemptsLimitEnabled' => true,
            'authUsernameFailedAttemptsDelay' => $delay,
            'authMaxUsernameFailedAttemptNumber' => 3,
        ]);
        $configWriter->save();

        $delayUtil = $this->createMock(DelayUtil::class);

        $app = $this->createApplication(
            binding: new class ($delayUtil) implements BindingProcessor {

                public function __construct(private DelayUtil $delayUtil) {}

                public function process(Binder $binder): void
                {
                    $binder->bindInstance(DelayUtil::class, $this->delayUtil);
                }
            },
            reuse: true,
        );
        $this->setApplication($app);

        $delayUtil->expects($this->once())
            ->method('delay')
            ->with($delay * 1000);

        $username = 'test';

        $data = AuthenticationData::create()
            ->withUsername($username)
            ->withPassword('1');

        $time = microtime(true);

        $authentication = $this->getInjectableFactory()->create(Authentication::class);

        $request = $this->createApiRequest($time, '1.0.0.1', $username);
        $response = $this->createMock(Response::class);
        $authentication->login($data, $request, $response);

        $request = $this->createApiRequest($time, '1.0.0.2', $username);
        $response = $this->createMock(Response::class);
        $authentication->login($data, $request, $response);

        $request = $this->createApiRequest($time, '1.0.0.3', $username);
        $response = $this->createMock(Response::class);
        $authentication->login($data, $request, $response);

        $request = $this->createApiRequest($time, '1.0.0.4', $username);
        $response = $this->createMock(Response::class);
        $authentication->login($data, $request, $response);
    }

    private function createApiRequest(float $time, string $ipAddress, string $username): RequestWrapper
    {
        $authorization = 'Basic ' . base64_encode($username . ':1');

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://localhost/api/v1/App/user', [
            'REMOTE_ADDR' => $ipAddress,
            'REQUEST_TIME_FLOAT' => $time,
        ]);

        $request = $request->withHeader(HeaderKey::AUTHORIZATION, $authorization);

        return new RequestWrapper($request);
    }
}
