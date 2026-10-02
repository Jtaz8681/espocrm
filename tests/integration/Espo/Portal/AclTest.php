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

namespace tests\integration\Espo\Portal;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SearchParams;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use tests\integration\Core\BaseTestCase;

class AclTest extends BaseTestCase
{
    public function testAccessContact(): void
    {
        $em = $this->getEntityManager();

        $contact = $em->createEntity('Contact', []);
        $portal = $em->createEntity('Portal', [
            'name' => 'Portal',
        ]);

        $this->createUser([
            'userName' => 'tester',
            'portalsIds' => [$portal->getId()],
            'contactId' => $contact->getId(),
        ], [
            'data' => [
                'Case' => [
                    'create' => 'no',
                    'read' => 'contact',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'contact',
                ]
            ],
        ], true);

        $this->auth(
            userName: 'tester',
            portalId: $portal->getId(),
        );

        $app = $this->createApplication(
            portalId: $portal->getId(),
            reuse: true
        );

        $this->setApplication($app);

        $em = $this->getEntityManager();

        $acl = $this->getContainer()->getByClass(Acl::class);

        $case1 = $em->createEntity('Case', [
            'contactId' => $contact->getId(),
        ], ['createdById' => '1']);
        $case2 = $em->createEntity('Case', [
            'contactsIds' => [$contact->getId()],
        ], ['createdById' => '1']);
        $case3 = $em->createEntity('Case', [
        ], ['createdById' => '1']);
        $case4 = $em->createEntity('Case', [
            'contactsIds' => [$contact->getId()],
            'isInternal' => true,
        ], ['createdById' => '1']);

        $this->assertFalse($acl->check('Case', 'create'));
        $this->assertFalse($acl->check('Case', 'edit'));
        $this->assertFalse($acl->check('Case', 'delete'));

        $this->assertTrue($acl->check($case1, 'read'));
        $this->assertTrue($acl->check($case2, 'read'));
        $this->assertFalse($acl->check($case3, 'read'));
        $this->assertFalse($acl->check($case4, 'read'));

        $service = $app->getContainer()->getByClass(ServiceContainer::class)->get('Case');

        $result = $service->find(SearchParams::create());

        $idList = [];
        foreach ($result->getCollection() as $e) {
            $idList[] = $e->getId();
        }

        $this->assertTrue(in_array($case1->getId(), $idList));
        $this->assertTrue(in_array($case2->getId(), $idList));
        $this->assertFalse(in_array($case3->getId(), $idList));
        $this->assertFalse(in_array($case4->getId(), $idList));
    }

    public function testAccessAccount(): void
    {
        $em = $this->getEntityManager();

        $contact = $em->createEntity('Contact', []);
        $account = $em->createEntity('Account', []);
        $portal = $em->createEntity('Portal', [
            'name' => 'Portal',
        ]);

        $this->createUser([
            'userName' => 'tester',
            'portalsIds' => [$portal->getId()],
            'contactId' => $contact->getId(),
            'accountsIds' => [$account->getId()],
        ], [
            'data' => [
                'Case' => [
                    'create' => 'no',
                    'read' => 'account',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'account',
                ]
            ],
        ], true);

        $this->auth(
            userName: 'tester',
            portalId: $portal->getId(),
        );

        $app = $this->createApplication(
            portalId: $portal->getId(),
            reuse: true
        );

        $this->setApplication($app);

        $em = $this->getEntityManager();

        $acl = $this->getContainer()->getByClass(Acl::class);

        $case1 = $em->createEntity('Case', [
            'contactId' => $contact->getId(),
        ], ['createdById' => '1']);
        $case2 = $em->createEntity('Case', [
            'contactsIds' => [$contact->getId()],
        ], ['createdById' => '1']);
        $case3 = $em->createEntity('Case', [
        ], ['createdById' => '1']);
        $case4 = $em->createEntity('Case', [
            'accountId' => $account->getId(),
        ], ['createdById' => '1']);
        $case5 = $em->createEntity('Case', [
            'accountId' => $account->getId(),
            'isInternal' => true,
        ], ['createdById' => '1']);

        $this->assertFalse($acl->check('Case', 'create'));
        $this->assertFalse($acl->check('Case', 'edit'));
        $this->assertFalse($acl->check('Case', 'delete'));

        $this->assertTrue($acl->check($case1, 'read'));
        $this->assertTrue($acl->check($case2, 'read'));
        $this->assertFalse($acl->check($case3, 'read'));
        $this->assertTrue($acl->check($case4, 'read'));
        $this->assertFalse($acl->check($case5, 'read'));

        $service = $app->getContainer()->getByClass(ServiceContainer::class)->get('Case');

        $result = $service->find(SearchParams::create());

        $idList = [];
        foreach ($result->getCollection() as $e) {
            $idList[] = $e->getId();
        }

        $this->assertTrue(in_array($case1->getId(), $idList));
        $this->assertTrue(in_array($case2->getId(), $idList));
        $this->assertFalse(in_array($case3->getId(), $idList));
        $this->assertTrue(in_array($case4->getId(), $idList));
        $this->assertFalse(in_array($case5->getId(), $idList));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testAccessOwn(): void
    {
        $em = $this->getEntityManager();

        $contact = $em->createEntity('Contact', []);
        $account = $em->createEntity('Account', []);
        $portal = $em->createEntity('Portal', [
            'name' => 'Portal',
        ]);

        $this->createUser([
            'userName' => 'tester',
            'portalsIds' => [$portal->getId()],
            'contactId' => $contact->getId(),
            'accountsIds' => [$account->getId()],
        ], [
            'data' => [
                'Case' => [
                    'create' => 'no',
                    'read' => 'own',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'own',
                ]
            ],
        ], true);

        $this->auth(
            userName: 'tester',
            portalId: $portal->getId(),
        );

        $app = $this->createApplication(
            portalId: $portal->getId(),
            reuse: true
        );

        $this->setApplication($app);

        $em = $this->getEntityManager();

        $acl = $this->getContainer()->getByClass(Acl::class);
        $user = $this->getContainer()->getByClass(User::class);

        $case1 = $em->createEntity('Case', [
            'contactId' => $contact->getId(),
        ], ['createdById' => '1']);
        $case2 = $em->createEntity('Case', [
            'contactsIds' => [$contact->getId()],
        ], ['createdById' => '1']);
        $case3 = $em->createEntity('Case', [
        ], ['createdById' => '1']);
        $case4 = $em->createEntity('Case', [
            'accountId' => $account->getId(),
        ], ['createdById' => '1']);
        $case5 = $em->createEntity('Case', [
            'accountId' => $account->getId(),
        ], ['createdById' => $user->getId()]);
        $case6 = $em->createEntity('Case', [
            'accountId' => $account->getId(),
            'isInternal' => true,
        ], ['createdById' => '1']);

        $this->assertFalse($acl->check('Case', 'create'));
        $this->assertFalse($acl->check('Case', 'edit'));
        $this->assertFalse($acl->check('Case', 'delete'));

        $this->assertFalse($acl->check($case1, 'read'));
        $this->assertFalse($acl->check($case2, 'read'));
        $this->assertFalse($acl->check($case3, 'read'));
        $this->assertFalse($acl->check($case4, 'read'));
        $this->assertTrue($acl->check($case5, 'read'));
        $this->assertFalse($acl->check($case6, 'read'));

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->get('Case');

        $result = $service->find(SearchParams::create());

        $idList = [];

        foreach ($result->getCollection() as $e) {
            $idList[] = $e->getId();
        }

        $this->assertFalse(in_array($case1->getId(), $idList));
        $this->assertFalse(in_array($case2->getId(), $idList));
        $this->assertFalse(in_array($case3->getId(), $idList));
        $this->assertFalse(in_array($case4->getId(), $idList));
        $this->assertTrue(in_array($case5->getId(), $idList));
        $this->assertFalse(in_array($case6->getId(), $idList));
    }

    public function testCreateCase(): void
    {
        $em = $this->getEntityManager();

        $contact = $em->createEntity('Contact');
        $account = $em->createEntity('Account');

        $contactNotOwn = $em->createEntity('Contact');
        $accountNotOwn = $em->createEntity('Account');

        $portal = $em->createEntity('Portal', [
            'name' => 'Portal',
        ]);

        $this->createUser([
            'userName' => 'tester',
            'portalsIds' => [$portal->getId()],
            'contactId' => $contact->getId(),
            'accountsIds' => [$account->getId()],
        ], [
            'data' => [
                'Case' => [
                    'create' => 'yes',
                    'read' => 'own',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'own',
                ]
            ],
        ], true);

        $this->auth(
            userName: 'tester',
            portalId: $portal->getId(),
        );

        $app = $this->createApplication(
            portalId: $portal->getId(),
            reuse: true,
        );

        $this->setApplication($app);

        $caseService = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(CaseObj::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $caseService->create((object) [
            'name' => 'Test 1',
            'accountId' => $account->getId(),
            'contactId' => $contact->getId(),
            'contactsIds' => [$contact->getId()],
        ], CreateParams::create());

        $isThrown = false;

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $caseService->create((object) [
                'name' => 'Test 1',
                'accountId' => $accountNotOwn->getId(),
                'contactId' => $contactNotOwn->getId(),
                'contactsIds' => [$contactNotOwn->getId()],
            ], CreateParams::create());
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);
    }
}
