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

namespace tests\integration\Espo\Tools\Activities;

use Espo\Core\Api\Request;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Modules\Crm\Tools\Activities\Api\GetComposeAddressList;
use tests\integration\Core\BaseTestCase;

class ComposeEmailAddressListTest extends BaseTestCase
{
    public function testGetGetAddressList(): void
    {
        $em = $this->getEntityManager();

        $account = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'Test',
            'emailAddress' => 'test@test.com',
        ]);

        $case = $em->createEntity(CaseObj::ENTITY_TYPE, [
            'accountId' => $account->getId(),
        ]);

        $action = $this->getInjectableFactory()
            ->create(GetComposeAddressList::class);

        $request = $this->createMock(Request::class);

        $request
            ->expects($this->any())
            ->method('getRouteParam')
            ->willReturnMap([
                ['parentType', CaseObj::ENTITY_TYPE],
                ['id', $case->getId()]
            ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $action->process($request);

        $list = json_decode($response->getBody());

        $this->assertEquals([
            (object) [
                'emailAddress' => 'test@test.com',
                'name' => $account->get('name'),
                'entityId' => $account->getId(),
                'entityType' => Account::ENTITY_TYPE,
            ]
        ], $list);
    }
}
