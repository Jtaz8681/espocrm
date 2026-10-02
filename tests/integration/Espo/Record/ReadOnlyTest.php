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

namespace tests\integration\Espo\Record;

use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class ReadOnlyTest extends BaseTestCase
{
    public function testReadOnly(): void
    {
        $metadata = $this->getContainer()->getByClass(Metadata::class);
        $metadata->set('entityDefs', Account::ENTITY_TYPE, [
            'fields' => [
                'name' => [
                    'readOnlyAfterCreate' => true,
                ],
                'type' => [
                    'readOnly' => true,
                ],
            ]
        ]);
        $metadata->save();

        $this->reCreateApplication(reuse: true);

        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Account::class);

        $account = $service->create((object) [
            'name' => 'Test',
            'type' => 'Customer',
        ], CreateParams::create())->getEntity();

        $this->assertEquals('Test', $account->get('name'));
        $this->assertNull($account->get('type'));

        $account = $service->update($account->getId(), (object) [
            'name' => 'Test 1',
            'billingAddressCity' => 'Hello',
        ], UpdateParams::create())->getEntity();

        $this->assertEquals('Test', $account->get('name'));
        $this->assertEquals('Hello', $account->get('billingAddressCity'));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testReadOnlyDynamicLogic(): void
    {
        $metadata = $this->getMetadata();

        $metadata->set('logicDefs', Account::ENTITY_TYPE, [
            'fields' => [
                'description' => [
                    'readOnlySaved' => [
                        'conditionGroup' => [
                            [
                                'type' => 'equals',
                                'attribute' => 'type',
                                'value' => 'Customer',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $metadata->save();

        $this->reCreateApplication(reuse: true);

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->getByClass(Account::class);

        $account = $service->create((object) [
            'name' => 'Test',
            'description' => '1',
        ], CreateParams::create())->getEntity();

        $service->update($account->getId(), (object) [
            'type' => 'Customer',
        ], UpdateParams::create());

        $account = $service->update($account->getId(), (object) [
            'description' => '2',
        ], UpdateParams::create());

        $this->assertEquals('1', $account->getValueMap()->description);
    }
}
