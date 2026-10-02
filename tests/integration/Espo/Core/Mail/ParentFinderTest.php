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

namespace tests\integration\Espo\Core\Mail;

use Espo\Core\Mail\Importer\DefaultParentFinder;
use Espo\Core\Mail\Message;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use tests\integration\Core\BaseTestCase;

class ParentFinderTest extends BaseTestCase
{
    public function testReplied(): void
    {
        $em = $this->getEntityManager();

        $account = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'Test'
        ]);

        $emailOne = $em->createEntity(Email::ENTITY_TYPE, [
            'parentId' => $account->getId(),
            'parentType' => $account->getEntityType(),
            'status' => Email::STATUS_ARCHIVED,
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->set([
            'repliedId' => $emailOne->getId(),
        ]);

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Account::class, $parent);
        $this->assertEquals($account->getId(), $parent->getId());
    }

    public function testReferences(): void
    {
        $em = $this->getEntityManager();

        $account = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'Test'
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $message
            ->expects($this->once())
            ->method('getHeader')
            ->with('References')
            ->willReturn('Account/' . $account->getId(). '/1667915635/6605@espo');

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Account::class, $parent);
        $this->assertEquals($account->getId(), $parent->getId());
    }

    public function testFromAddressAccount(): void
    {
        $em = $this->getEntityManager();

        /** @var Account $subject */
        $subject = $em->createEntity(Account::ENTITY_TYPE, [
            'emailAddress' => 'subject@test.com',
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->setFromAddress($subject->getEmailAddress());

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Account::class, $parent);
        $this->assertEquals($subject->getId(), $parent->getId());
    }

    public function testFromAddressContact(): void
    {
        $em = $this->getEntityManager();

        /** @var Contact $subject */
        $subject = $em->createEntity(Contact::ENTITY_TYPE, [
            'emailAddress' => 'subject@test.com',
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->setFromAddress($subject->getEmailAddress());

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Contact::class, $parent);
        $this->assertEquals($subject->getId(), $parent->getId());
    }

    public function testFromAddressLead(): void
    {
        $em = $this->getEntityManager();

        /** @var Lead $subject */
        $subject = $em->createEntity(Lead::ENTITY_TYPE, [
            'emailAddress' => 'subject@test.com',
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->setFromAddress($subject->getEmailAddress());

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Lead::class, $parent);
        $this->assertEquals($subject->getId(), $parent->getId());
    }

    public function testReplyToAddress(): void
    {
        $em = $this->getEntityManager();

        /** @var Lead $subject */
        $subject = $em->createEntity(Lead::ENTITY_TYPE, [
            'emailAddress' => 'subject@test.com',
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->setFromAddress('any@address.com');
        $email->addReplyToAddress($subject->getEmailAddress());

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Lead::class, $parent);
        $this->assertEquals($subject->getId(), $parent->getId());
    }

    public function testToAddress(): void
    {
        $em = $this->getEntityManager();

        /** @var Lead $subject */
        $subject = $em->createEntity(Lead::ENTITY_TYPE, [
            'emailAddress' => 'subject@test.com',
        ]);

        /** @var Email $email */
        $email = $em->getNewEntity(Email::ENTITY_TYPE);

        $email->setFromAddress('any@address.com');
        $email->addToAddress($subject->getEmailAddress());

        $finder = $this->getInjectableFactory()->create(DefaultParentFinder::class);

        $message = $this->createMock(Message::class);

        $parent = $finder->find($email, $message);

        $this->assertInstanceOf(Lead::class, $parent);
        $this->assertEquals($subject->getId(), $parent->getId());
    }
}
