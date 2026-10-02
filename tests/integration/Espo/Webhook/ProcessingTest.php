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

use Espo\Core\InjectableFactory;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Webhook\Queue;
use Espo\Core\Webhook\Sender;
use Espo\ORM\EntityManager;
use Espo\Entities\Webhook;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class ProcessingTest extends BaseTestCase
{
    public function testProcessing1(): void
    {
        $user = $this->createUser(
            [
                'userName' => 'test',
                'password' => '1',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => [
                        'create' => 'yes',
                        'read' => 'own',
                    ],
                ],
            ]
        );

        $em = $this->getContainer()->getByClass(EntityManager::class);

        $em->createEntity(Webhook::ENTITY_TYPE, [
            'event' => 'Account.create',
            'userId' => $user->getId(),
            'url' => 'https://test.com',
            'skipOwn' => true,
        ]);

        $em->createEntity(Webhook::ENTITY_TYPE, [
            'event' => 'Account.update',
            'userId' => $user->getId(),
            'url' => 'https://test.com',
            'skipOwn' => true,
        ]);

        $this->getDataManager()->clearCache();

        $this->authenticate(null);

        $em = $this->getEntityManager();

        $account1 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test1',
            'assignedUserId' => $user->getId(),
        ]);

        $account2 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test2',
            'assignedUserId' => $user->getId(),
        ]);

        $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test3',
        ]);

        $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test_skip',
            'assignedUserId' => $user->getId(),
        ], [SaveOption::CREATED_BY_ID => $user->getId()]);

        $dataList1 = [
            $account1->getValueMap(),
            $account2->getValueMap(),
        ];

        $account1->set('name', 'test-1-changed');
        $em->saveEntity($account1);

        $account1->set('description', 'test-skip');
        $em->saveEntity($account1, [SaveOption::MODIFIED_BY_ID => $user->getId()]);

        $dataList2 = [
            (object) [
                'name' => $account1->get('name'),
                'modifiedById' => 'system',
                'modifiedByName' => 'System',
                'id' => $account1->getId(),
            ],
        ];

        $sender = $this->createMock(Sender::class);

        $queue = $this->getContainer()
            ->getByClass(InjectableFactory::class)
            ->createWith(Queue::class, [
                'sender' => $sender,
            ]);

        $invokedCount = $this->exactly(2);

        $notSame = false;

        $sender
            ->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(function (Webhook $webhook, $dataList) use ($invokedCount, $dataList1, $dataList2, &$notSame) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    if (count($dataList) !== count($dataList1)) {
                        $notSame = true;
                    }

                    if ('Account.create' !== $webhook->getEvent()) {
                        $notSame = true;
                    }

                    return 200;
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    if (count($dataList) !== count($dataList2)) {
                        $notSame = true;
                    }

                    if ('Account.update' !== $webhook->getEvent()) {
                        $notSame = true;
                    }

                    return 200;
                }

                return 0;
            });

        $queue->process();
        $queue->process();

        $this->assertFalse($notSame);
    }

    public function testProcessing2(): void
    {
        $user = $this->createUser(
            [
                'userName' => 'test',
                'password' => '1',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => [
                        'create' => 'yes',
                        'read' => 'own',
                    ],
                ],
            ]
        );

        $this->getDataManager()->clearCache();

        $this->authenticate(null);

        $em = $this->getEntityManager();

        $account1 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test1',
            'assignedUserId' => $user->getId(),
        ]);

        $account2 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test2',
            'assignedUserId' => $user->getId(),
        ]);

        $em->createEntity(Webhook::ENTITY_TYPE, [
            'event' => 'Account.delete',
            'userId' => $user->getId(),
            'url' => 'https://test.com',
            'skipOwn' => true,
        ]);

        $this->getDataManager()->clearCache();

        $this->authenticate(null);

        $em = $this->getEntityManager();

        $em->removeEntity($account1);
        $em->removeEntity($account2, [SaveOption::MODIFIED_BY_ID => $user->getId()]);

        $sender = $this->createMock(Sender::class);

        $queue = $this->getInjectableFactory()
            ->createWith(Queue::class, [
                'sender' => $sender,
            ]);

        $dataList1 = [
            (object) [
                'id' => $account1->getId(),
            ]
        ];

        $invokedCount = $this->exactly(1);

        $notSame = false;

        $sender
            ->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(function (Webhook $webhook, $dataList) use ($invokedCount, $dataList1, &$notSame) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    if ('Account.delete' !== $webhook->getEvent()) {
                        $notSame = true;
                    }

                    if (json_encode($dataList) !== json_encode($dataList1)) {
                        $notSame = true;
                    }

                    return 200;
                }

                return 0;
            });

        $queue->process();
        $queue->process();

        $this->assertFalse($notSame);
    }

    public function testProcessing3(): void
    {
        $user = $this->createUser(
            [
                'userName' => 'test',
                'password' => '1',
            ],
            [
                'data' => [
                    'Webhook' => true,
                    'Account' => [
                        'create' => 'yes',
                        'read' => 'own',
                    ],
                ],
            ]
        );


        $em = $this->getEntityManager();

        $account1 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'test1',
            'assignedUserId' => $user->getId(),
        ]);

        $em->createEntity(Webhook::ENTITY_TYPE, [
            'event' => 'Account.fieldUpdate.name',
            'userId' => $user->getId(),
            'url' => 'https://test.com',
        ]);

        $this->getDataManager()->clearCache();

        $this->authenticate(null);

        $em = $this->getEntityManager();

        $account1->set('name', 'test-1-changed');

        $em->saveEntity($account1);

        $dataList1 = [
            (object) [
                'id' => $account1->getId(),
                'name' => 'test-1-changed',
            ]
        ];

        $sender = $this->createMock(Sender::class);

        $queue = $this->getInjectableFactory()
            ->createWith(Queue::class, [
                'sender' => $sender,
            ]);

        $invokedCount = $this->exactly(1);

        $notSame = false;

        $sender
            ->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(function (Webhook $webhook, $dataList) use ($invokedCount, $dataList1, &$notSame) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    if (json_encode($dataList) !== json_encode($dataList1)) {
                        $notSame = true;
                    }

                    if ('Account.fieldUpdate.name' !== $webhook->getEvent()) {
                        $notSame = true;
                    }

                    return 200;
                }

                return 0;
            });

        $queue->process();
        $queue->process();

        $this->assertFalse($notSame);
    }
}
