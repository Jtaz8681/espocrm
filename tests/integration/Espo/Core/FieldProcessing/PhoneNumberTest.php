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
use Espo\Core\Field\PhoneNumber;
use Espo\Core\Field\PhoneNumberGroup;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Modules\Crm\Entities\Contact;

use tests\integration\Core\BaseTestCase;

class PhoneNumberTest extends BaseTestCase
{
    public function testPhoneNumber1(): void
    {
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        $contact = $entityManager->getNewEntity(Contact::ENTITY_TYPE);
        $contact->set('phoneNumber', '+1');
        $entityManager->saveEntity($contact);

        $this->assertEquals('+1', $contact->get('phoneNumber'));

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group1 PhoneNumberGroup */
        $group1 = $contact->getPhoneNumberGroup();

        $this->assertEquals(1, $group1->getCount());

        $this->assertEquals('+1', $group1->getPrimary()->getNumber());

        $group2 = PhoneNumberGroup
            ::create()
            ->withAdded(
                PhoneNumber::create('+2')->invalid()
            )
            ->withAdded(
                PhoneNumber::create('+1')->optedOut()
            );

        $contact->setValueObject('phoneNumber', $group2);

        $entityManager->saveEntity($contact);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group3 PhoneNumberGroup */
        $group3 = $contact->getPhoneNumberGroup();

        $this->assertEquals(2, $group3->getCount());
        $this->assertEquals('+2', $group3->getPrimary()->getNumber());
        $this->assertTrue($group3->getPrimary()->isInvalid());
        $this->assertTrue($group3->getList()[1]->isOptedOut());

        $group4 = PhoneNumberGroup
            ::create()
            ->withAdded(
                PhoneNumber::create('+2')->invalid()
            );

        $contact->setValueObject('phoneNumber', $group4);

        $entityManager->saveEntity($contact);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        /* @var $group5 PhoneNumberGroup */
        $group5 = $contact->getPhoneNumberGroup();

        $this->assertEquals(1, $group5->getCount());
    }

    public function testPrimaryFirst(): void
    {
        $em = $this->getEntityManager();

        $lead = $em->getRDBRepositoryByClass(Lead::class)
            ->getNew();

        $lead->set('phoneNumberData', [
            (object) ['phoneNumber' => '+0000000001'],
            (object) ['phoneNumber' => '+0000000002'],
            (object) ['phoneNumber' => '+0000000003'],
            (object) ['phoneNumber' => '+0000000004'],
        ]);

        $em->saveEntity($lead);
        $em->refreshEntity($lead);

        $this->assertEquals('+0000000001', $lead->getPhoneNumber());
    }

    public function testPhoneNumber2(): void
    {
        $service = $this->getContainer()->getByClass(ServiceContainer::class)->getByClass(Contact::class);
        $em = $this->getEntityManager();

        /** @var Contact $contact */
        $contact = $em->createEntity(Contact::ENTITY_TYPE);

        /** @noinspection PhpUnhandledExceptionInspection */
        $service->update($contact->getId(), (object) [
            'phoneNumber' => '+11111111111',
        ], UpdateParams::create());

        $em->refreshEntity($contact);

        $this->assertEquals('+11111111111', $contact->getPhoneNumber());
    }
}
