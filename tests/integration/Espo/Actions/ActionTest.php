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

use Espo\Core\Action\Actions\Merge\Merger;
use Espo\Core\Action\Api\PostProcess;
use Espo\Core\Action\Params;
use Espo\Core\Application;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\PhoneNumber;
use Espo\Core\ORM\EntityManager;

use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use tests\integration\Core\BaseTestCase;

class ActionTest extends BaseTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Merger */
    private $merger;

    private ?PostProcess $action = null;

    private function init(): void
    {
        $this->entityManager = $this->getApplication()
            ->getContainer()
            ->getByClass(EntityManager::class);

        $this->action = $this->getInjectableFactory()->create(PostProcess::class);

        $this->merger = $this->getApplication()
            ->getInjectableFactory()
            ->create(Merger::class);
    }

    public function testActionNotAllowed(): void
    {
        $this->init();

        $data = [
            'entityType' => 'Role',
            'action' => 'convertCurrency',
            'id' => 'arbitrary-id',
            'data' => (object) [],
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $this->expectException(Forbidden::class);

        $this->action->process($request);
    }

    public function testActionMerge1(): void
    {
        $this->createUser('tester', [
            'assignmentPermission' => 'all',
            'data' => [
                'Contact' => [
                    'create' => 'all',
                    'read' => 'all',
                    'edit' => 'all',
                    'delete' => 'all',
                ],
            ],
        ]);

        $this->authenticate('tester');

        $this->init();

        $team1 = $this->entityManager->createEntity('Team', []);
        $team2 = $this->entityManager->createEntity('Team', []);

        $account1 = $this->entityManager->createEntity('Account', []);
        $account2 = $this->entityManager->createEntity('Account', []);

        /* @var $contact1 Contact */
        $contact1 = $this->entityManager->getNewEntity('Contact');

        $emailAddressGroup1 = $contact1->getEmailAddressGroup()
            ->withAdded(EmailAddress::create('c1a@test.com'))
            ->withAdded(EmailAddress::create('c1b@test.com')->invalid());

        $contact1->setEmailAddressGroup($emailAddressGroup1);

        $phoneNumberGroup1 = $contact1->getPhoneNumberGroup()
            ->withAdded(PhoneNumber::create('+11000000000'))
            ->withAdded(PhoneNumber::create('+12000000000')->invalid());

        $contact1->setPhoneNumberGroup($phoneNumberGroup1);

        $contact1->set('accountId', $account1->getId());

        $contact1->set('teamsIds', [$team1->getId()]);

        /* @var $contact2 Contact */
        $contact2 =  $this->entityManager->getNewEntity('Contact');

        $emailAddressGroup2 = $contact1->getEmailAddressGroup()
            ->withAdded(EmailAddress::create('c2a@test.com'))
            ->withAdded(EmailAddress::create('c2b@test.com')->optedOut());

        $contact2->setEmailAddressGroup($emailAddressGroup2);

        $phoneNumberGroup2 = $contact2->getPhoneNumberGroup()
            ->withAdded(PhoneNumber::create('+11100000000'))
            ->withAdded(PhoneNumber::create('+12100000000')->optedOut());

        $contact2->setPhoneNumberGroup($phoneNumberGroup2);

        $contact2->set('accountId', $account2->getId());

        $contact2->set('teamsIds', [$team2->getId()]);

        $this->entityManager->saveEntity($contact1);
        $this->entityManager->saveEntity($contact2);

        $this->entityManager->createEntity('Note', [
            'type' => 'Post',
            'parentType' => 'Contact',
            'parentId' => $contact1->getId(),
        ]);

        $note2 = $this->entityManager->createEntity('Note', [
            'type' => 'Post',
            'parentType' => 'Contact',
            'parentId' => $contact2->getId(),
        ]);

        $opportunity1 = $this->entityManager->createEntity('Opportunity', []);
        $opportunity2 = $this->entityManager->createEntity('Opportunity', []);

        $this->entityManager
            ->getRDBRepository('Contact')
            ->getRelation($contact1, 'opportunities')
            ->relate($opportunity1);

        $this->entityManager
            ->getRDBRepository('Contact')
            ->getRelation($contact2, 'opportunities')
            ->relate($opportunity2);

        $data = [
            'entityType' => 'Contact',
            'action' => 'merge',
            'id' => $contact1->getId(),
            'data' => (object) [
                'attributes' => (object) [
                    'description' => 'merged',
                ],
                'sourceIdList' => [$contact2->getId()],
            ],
        ];

        $request = $this->createRequest(
            'POST',
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $this->action->process($request);

        /* @var $contact1Reloaded Contact */
        $contact1Reloaded = $this->entityManager->getEntityById('Contact', $contact1->getId());
        $contact2Reloaded = $this->entityManager->getEntityById('Contact', $contact2->getId());

        $this->assertEquals('merged', $contact1Reloaded->get('description'));

        $this->assertNull($contact2Reloaded);

        $emailAddressGroup = $contact1Reloaded->getEmailAddressGroup();
        $phoneNumberGroup = $contact1Reloaded->getPhoneNumberGroup();

        $this->assertEquals(4, $emailAddressGroup->getCount());
        $this->assertEquals(4, $phoneNumberGroup->getCount());

        $this->assertEquals(
            'c1a@test.com',
            $emailAddressGroup->getPrimary()->getAddress()
        );

        $this->assertEquals(
            '+11000000000',
            $phoneNumberGroup->getPrimary()->getNumber()
        );

        $this->assertTrue(
            $emailAddressGroup->getByAddress('c2b@test.com')->isOptedOut()
        );

        $this->assertTrue(
            $emailAddressGroup->getByAddress('c1b@test.com')->isInvalid()
        );

        $this->assertTrue(
            $phoneNumberGroup->getByNumber('+12100000000')->isOptedOut()
        );

        $this->assertTrue(
            $phoneNumberGroup->getByNumber('+12000000000')->isInvalid()
        );

        $this->assertEquals(
            $contact1->getId(),
            $this->entityManager
                ->getEntityById('Note', $note2->getId())
                ->get('parentId')
        );

        $this->assertEquals(
            2,
            $this->entityManager
                ->getRDBRepository('Contact')
                ->getRelation($contact1Reloaded, 'opportunities')
                ->count()
        );

        $this->assertEquals(
            2,
            $this->entityManager
                ->getRDBRepository('Contact')
                ->getRelation($contact1Reloaded, 'teams')
                ->count()
        );

        $this->assertEquals(
            2,
            count($contact1Reloaded->getLinkMultipleIdList('accounts'))
        );
    }

    public function testMergeRelationshipColumns(): void
    {
        $em = $this->getEntityManager();

        $account = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $accountSource = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $contact = $em->getRDBRepositoryByClass(Contact::class)->getNew();

        $em->saveEntity($account);
        $em->saveEntity($accountSource);
        $em->saveEntity($contact);

        $em->getRelation($accountSource, 'contacts')
            ->relate($contact, [
                'role' => 'Tester',
                'isInactive' => true,
            ]);

        $merger = $this->getInjectableFactory()->create(Merger::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $merger->process(
            new Params(Account::ENTITY_TYPE, $account->getId()),
            [$accountSource->getId()],
            (object) []
        );

        $em->refreshEntity($account);

        $relation = $em->getRelation($account, 'contacts');

        $this->assertEquals(
            'Tester',
            $relation->getColumn($contact, 'role')
        );

        $this->assertTrue(
            $relation->getColumn($contact, 'isInactive')
        );
    }

    public function testMergeNoEditAccess(): void
    {
        $this->createUser('tester', [
            'assignmentPermission' => 'all',
            'data' => [
                'Contact' => [
                    'create' => 'own',
                    'read' => 'all',
                    'edit' => 'own',
                    'delete' => 'no',
                ],
            ],
        ]);

        $this->authenticate('tester');

        $this->init();

        $contact1 = $this->entityManager->createEntity('Contact', []);
        $contact2 = $this->entityManager->createEntity('Contact', []);

        $params = new Params('Contact', $contact1->getId());

        $this->expectException(Forbidden::class);

        $this->merger->process($params, [$contact2->getId()], (object) []);
    }

    public function testMergeNoDeleteAccess(): void
    {
        $this->createUser('tester', [
            'assignmentPermission' => 'all',
            'data' => [
                'Contact' => [
                    'create' => 'own',
                    'read' => 'all',
                    'edit' => 'all',
                    'delete' => 'no',
                ],
            ],
        ]);

        $this->authenticate('tester');

        $this->init();

        $contact1 = $this->entityManager->createEntity('Contact', []);
        $contact2 = $this->entityManager->createEntity('Contact', []);

        $params = new Params('Contact', $contact1->getId());

        $this->expectException(Forbidden::class);

        $this->merger->process($params, [$contact2->getId()], (object) []);
    }
}
