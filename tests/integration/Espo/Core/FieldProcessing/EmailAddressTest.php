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

namespace tests\integration\Espo\Core\FieldProcessing;

use Espo\Core\ORM\EntityManager;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Modules\Crm\Entities\Contact;

use tests\integration\Core\BaseTestCase;

class EmailAddressTest extends BaseTestCase
{
    public function testEmailAddress1(): void
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->get('entityManager');

        $contact = $entityManager->createEntity('Contact', [
            'emailAddress' => 'test@test.com',
        ]);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group1 EmailAddressGroup */
        $group1 = $contact->getEmailAddressGroup();

        $this->assertEquals(1, $group1->getCount());

        $this->assertEquals('test@test.com', $group1->getPrimary()->getAddress());

        $group2 = EmailAddressGroup::create()
            ->withAdded(
                EmailAddress::create('test-a@test.com')->invalid()
            )
            ->withAdded(
                EmailAddress::create('test@test.com')->optedOut()
            );

        $contact->setValueObject('emailAddress', $group2);

        $entityManager->saveEntity($contact);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group3 EmailAddressGroup */
        $group3 = $contact->getEmailAddressGroup();

        $this->assertEquals(2, $group3->getCount());
        $this->assertEquals('test-a@test.com', $group3->getPrimary()->getAddress());
        $this->assertTrue($group3->getPrimary()->isInvalid());
        $this->assertTrue($group3->getList()[1]->isOptedOut());

        $group4 = EmailAddressGroup::create()
            ->withAdded(
                EmailAddress::create('test-a@test.com')->invalid()
            );

        $contact->setValueObject('emailAddress', $group4);

        $entityManager->saveEntity($contact);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group5 EmailAddressGroup */
        $group5 = $contact->getEmailAddressGroup();

        $this->assertEquals(1, $group5->getCount());
    }

    public function testEmailAddress2(): void
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->get('entityManager');

        $lead = $entityManager->createEntity('Lead', [
            'emailAddress' => 'test@test.com',
        ]);

        $contact = $entityManager->createEntity('Contact', [
            'emailAddress' => 'test@test.com',
        ]);

        $contactFetched = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group EmailAddressGroup */
        $group = $contactFetched->getEmailAddressGroup();

        $this->assertEquals(1, $group->getCount());

        $this->assertEquals('test@test.com', $group->getPrimary()->getAddress());
    }

    public function testPrimaryFirst(): void
    {
        $em = $this->getEntityManager();

        $lead = $em->getRDBRepositoryByClass(Lead::class)
            ->getNew();

        $lead->set('emailAddressData', [
            (object) ['emailAddress' => 'test-1@test.com'],
            (object) ['emailAddress' => 'test-2@test.com'],
            (object) ['emailAddress' => 'test-3@test.com'],
            (object) ['emailAddress' => 'test-4@test.com'],
        ]);

        $em->saveEntity($lead);
        $em->refreshEntity($lead);

        $this->assertEquals('test-1@test.com', $lead->getEmailAddress());
    }

    public function testEmailAddress3(): void
    {
        $service = $this->getContainer()->getByClass(ServiceContainer::class)->getByClass(Contact::class);
        $em = $this->getEntityManager();

        /** @var Contact $contact */
        $contact = $em->createEntity(Contact::ENTITY_TYPE);

        /** @noinspection PhpUnhandledExceptionInspection */
        $service->update($contact->getId(), (object) [
            'emailAddress' => 'test@test.com',
        ], UpdateParams::create());

        $em->refreshEntity($contact);

        $this->assertEquals('test@test.com', $contact->getEmailAddress());
    }
}
