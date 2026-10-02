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

namespace tests\integration\Espo\Webhook;

use Espo\Core\Api\ControllerActionProcessor;
use Espo\Core\Api\ResponseWrapper;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Exceptions\Forbidden;
use tests\integration\Core\BaseTestCase;

class AclTest extends BaseTestCase
{
    public function testRegularUserNoAccess(): void
    {
        $this->createUser(
            [
                'userName' => 'test',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => ['create'=> 'yes', 'read' => 'own'],
                ],
            ]
        );

        $this->authenticate('test');

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $this->expectException(Forbidden::class);

        $request = $this
            ->createRequest('POST', [], ['Content-Type' => 'application/json'], '{"event":"Account.create"}');

        $processor->process('Webhook', 'create', $request, $this->createResponse());
    }

    public function testApiUserNoAccess1()
    {
        $this->createUser(
            [
                'userName' => 'api',
                'type' => 'api',
                'authMethod' => 'ApiKey',
                'apiKey' => 'test-key',
            ],
            [
                'data' => [
                    'Webhook' => false,
                ],
            ]
        );

        $this->getDataManager()->clearCache();

        $request = $this->createRequest(
            'POST',
            [],
            [
                'Content-Type' => 'application/json',
                'X-Api-Key' => 'test-key',
            ],
            '{"event":"Account.create", "url": "https://test.com"}'
        );

        $this->authenticate(method: 'ApiKey', request: $request);

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $this->expectException(Forbidden::class);

        $processor->process('Webhook', 'create', $request, $this->createResponse());
    }

    public function testApiUserNoAccess2()
    {
        $this->createUser(
            [
                'userName' => 'api',
                'type' => 'api',
                'authMethod' => 'ApiKey',
                'apiKey' => 'test-key',
            ],
            [
                'data' => [
                    'Webhook' => false,
                    'Account' => false,
                ],
            ]
        );

        $this->getDataManager()->clearCache();

        $request = $this->createRequest(
            'POST',
            [],
            [
                'Content-Type' => 'application/json',
                'X-Api-Key' => 'test-key',
            ],
            '{"event":"Account.create", "url": "https://test.com"}'
        );

        $this->authenticate(method: 'ApiKey', request: $request);

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $this->expectException(Forbidden::class);

        $processor->process('Webhook', 'create', $request, $this->createResponse());
    }

    public function testApiUserHasAccess1()
    {
        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);
        $configWriter->set('webhookAllowedAddressList', ['test.com:443']);
        $configWriter->save();

        $this->createUser(
            [
                'userName' => 'api',
                'type' => 'api',
                'authMethod' => 'ApiKey',
                'apiKey' => 'test-key',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => ['create' => 'yes', 'read' => 'own'],
                ],
            ]
        );

        $request = $this->createRequest(
            'POST',
            [],
            [
                'Content-Type' => 'application/json',
                'X-Api-Key' => 'test-key',
            ],
            '{"event":"Account.create", "url": "https://test.com"}'
        );

        $this->authenticate(method: 'ApiKey', request: $request);

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $response = $this->createMock(ResponseWrapper::class);

        $response
            ->expects($this->once())
            ->method('writeBody');

        $processor->process('Webhook', 'create', $request, $response);
    }

    public function testApiUserHasAccessDelete(): void
    {
        $user = $this->createUser(
            [
                'userName' => 'api',
                'type' => 'api',
                'authMethod' => 'ApiKey',
                'apiKey' => 'test-key',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => ['create' => 'yes', 'read' => 'own'],
                ],
            ]
        );

        $em = $this->getEntityManager();

        $webhook = $em->createEntity('Webhook', [
            'event' => 'Account.create',
            'url' => 'https://test.com',
            'userId' => $user->getId(),
        ]);

        $request = $this->createRequest(
            'DELETE',
            [],
            [
                'Content-Type' => 'application/json',
                'X-Api-Key' => 'test-key',
            ],
            null,
            [
                'id' => $webhook->getId(),
            ]
        );

        $this->authenticate(method: 'ApiKey', request: $request);

        $em = $this->getEntityManager();

        $response = $this->createMock(ResponseWrapper::class);

        $processor = $this->getInjectableFactory()->create(ControllerActionProcessor::class);

        $processor->process('Webhook', 'delete', $request, $response);

        $fetchedWebhook = $em->getEntityById('Webhook', $webhook->getId());

        $this->assertNull($fetchedWebhook);
    }
}
