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

namespace tests\integration\Espo\Actions;

use Espo\Core\MassAction\Api\PostProcess;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;

use Espo\Core\MassAction\ServiceParams;
use Espo\Core\MassAction\Service;
use Espo\Core\MassAction\Params as MassActionParams;
use Espo\Core\MassAction\Jobs\Process as JobProcess;
use Espo\Core\Job\Job\Data as JobData;

use Espo\Core\Select\SearchParams;

use Espo\Core\Api\ResponseWrapper;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\ORM\EntityManager;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Tools\MassUpdate\Data as MassUpdateData;
use Espo\Tools\MassUpdate\Processor;
use tests\integration\Core\BaseTestCase;

class MassActionTest extends BaseTestCase
{
    /** @var EntityManager */
    private $entityManager;

    private ?PostProcess $action = null;

    private function init(): void
    {
        $this->entityManager = $this->getApplication()
            ->getContainer()
            ->getByClass(EntityManager::class);

        $this->action = $this->getInjectableFactory()->create(PostProcess::class);
    }

    private function getAdminUser(): User
    {
        $repository = $this->getContainer()
            ->getByClass(EntityManager::class)
            ->getRDBRepositoryByClass(User::class);

        $user = $repository
            ->where(['type' => User::TYPE_ADMIN])
            ->findOne();

        if (!$user) {
            $user = $repository->getNew();
            $user->set('userName', 'test-admin');
            $user->set('type', User::TYPE_ADMIN);

            $repository->save($user);
        }

        return $user;
    }

    public function testUpdate1(): void
    {
        $adminUser = $this->getAdminUser();

        $this->createUser([
            'userName' => 'admin-test',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-test');
        $this->init();

        $account = $this->entityManager->createEntity('Account', [
            'name' => 'test',
        ]);

        $data = [
            'entityType' => 'Account',
            'action' => 'update',
            'params' => [
                'ids' => [$account->getId()],
            ],
            'data' => [
                'assignedUserId' => $adminUser->getId(),
            ],
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $this->action->process($request);

        $accountReloaded = $this->entityManager->getEntityById('Account', $account->getId());

        $this->assertEquals($adminUser->getId(), $accountReloaded->get('assignedUserId'));
    }

    public function testUpdateNotAllowed(): void
    {
        $this->init();

        $data = [
            'entityType' => 'Role',
            'action' => 'update',
            'params' => [
                'ids' => ['arbitrary-id'],
            ],
            'data' => [
                'name' => '1'
            ],
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $this->createMock(ResponseWrapper::class);

        $this->expectException(Forbidden::class);

        $this->action->process($request);
    }

    public function testAcl1(): void
    {
        $em = $this->getEntityManager();

        $user = $this->createUser('tester', [
            'massUpdatePermission' => 'yes',
            'data' => [
                'Account' => [
                    'create' => 'no',
                    'read' => 'all',
                    'edit' => 'own',
                    'delete' => 'no',
                ],
            ],
        ]);

        $account1 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => '1',
            'assignedUserId' => $user->getId(),
        ]);

        $account2 = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => '2',
        ]);

        $this->authenticate('tester');

        $injectableFactory = $this->getInjectableFactory();

        $this->assertEquals($user->getId(), $this->getContainer()->getByClass(User::class)->getId());

        /** @var Service $service */
        $service = $injectableFactory->create(Service::class);

        $serviceParams = ServiceParams
            ::create(
                MassActionParams::createWithSearchParams('Account', SearchParams::create())
            )
            ->withIsIdle();

        $serviceResult = $service->process(
            'Account',
            'update',
            $serviceParams,
            (object) [
                'type' => 'Customer',
            ]
        );

        $this->assertFalse($serviceResult->hasResult());

        $massActionId = $serviceResult->getId();

        $this->authenticate(null);

        $this->assertEquals('system', $this->getContainer()->getByClass(User::class)->getId());

        $injectableFactory1 = $this->getInjectableFactory();

        $process = $injectableFactory1->create(JobProcess::class);

        $process->run(JobData::create()->withTargetId($massActionId));

        $em->refreshEntity($account1);
        $em->refreshEntity($account2);

        $this->assertEquals('Customer', $account1->get('type'));
        $this->assertEquals(null, $account2->get('type'));
    }

    public function testAcl2()
    {
        $user = $this->createUser('tester', [
            'massUpdatePermission' => 'yes',
            'data' => [
                'User' => [
                    'create' => 'no',
                    'read' => 'all',
                    'edit' => 'own',
                    'delete' => 'no',
                ],
            ],
        ]);

        $this->createUser('tester-1', []);

        $this->authenticate('tester');

        $injectableFactory = $this->getInjectableFactory();

        $this->assertEquals($user->getId(), $this->getContainer()->getByClass(User::class)->getId());

        $service = $injectableFactory->create(Service::class);

        $serviceParams = ServiceParams
            ::create(
                MassActionParams::createWithSearchParams('User', SearchParams::create())
            )
            ->withIsIdle();

        $serviceResult = $service->process(
            'User',
            'update',
            $serviceParams,
            (object) [
                'title' => 'Tester',
            ]
        );

        $this->assertFalse($serviceResult->hasResult());

        $massActionId = $serviceResult->getId();

        $this->authenticate(null);

        $this->assertEquals('system', $this->getContainer()->getByClass(User::class)->getId());

        $injectableFactory1 = $this->getInjectableFactory();

        $process = $injectableFactory1->create(JobProcess::class);

        // Mass-update for User entity type is allowed only for admins.
        $this->expectException(Error::class);

        $process->run(JobData::create()->withTargetId($massActionId));
    }

    public function testMassUpdateLinkAccess(): void
    {
        $user = $this->createUser('tester', [
            'massUpdatePermission' => 'yes',
            'data' => [
                'Contact' => [
                    'create' => 'no',
                    'read' => 'own',
                    'edit' => 'no',
                    'delete' => 'no'
                ],
                'Opportunity' => [
                    'create' => 'yes',
                    'read' => 'team',
                    'edit' => 'own',
                    'delete' => 'own'
                ],
            ],
        ]);


        $this->authenticate('tester');

        $contact1 = $this->getEntityManager()
            ->createEntity(Contact::ENTITY_TYPE, [
                'lastName' => 'Contact 1',
                'assignedUserId' => $user->getId(),
            ]);

        $contact2 = $this->getEntityManager()
            ->createEntity(Contact::ENTITY_TYPE, [
                'lastName' => 'Contact 2',
            ]);

        $opp1 = $this->getEntityManager()
            ->createEntity(Opportunity::ENTITY_TYPE, [
                'name' => 'Opp 1',
                'assignedUserId' => $user->getId(),
            ]);

        $opp2 = $this->getEntityManager()
            ->createEntity(Opportunity::ENTITY_TYPE, [
                'name' => 'Opp 2',
                'assignedUserId' => $user->getId(),
            ]);

        $processor = $this->getInjectableFactory()->create(Processor::class);

        $params = MassActionParams::createWithIds(Opportunity::ENTITY_TYPE, [$opp1->getId(), $opp2->getId()]);

        $data = MassUpdateData::create()
            ->with('contactsIds', [$contact1->getId()]);
        $result = $processor->process($params, $data);
        $this->assertEquals(2, $result->getCount());

        $data = MassUpdateData::create()
            ->with('contactsIds', [$contact2->getId()]);
        $result = $processor->process($params, $data);
        $this->assertEquals(0, $result->getCount());
    }
}
