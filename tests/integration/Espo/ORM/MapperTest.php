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

namespace tests\integration\Espo\ORM;

use Espo\ORM\EntityManager;
use tests\integration\Core\BaseTestCase;

class MapperTest extends BaseTestCase
{
    public function testRelate1()
    {
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        $account = $entityManager->getNewEntity('Account');
        $account->set('name', 'Test');
        $entityManager->saveEntity($account);

        $contact = $entityManager->getNewEntity('Contact');
        $contact->set('lastName', 'Test');
        $entityManager->saveEntity($contact);

        $entityManager->getRelation($account, 'contacts')->relate($contact);
        $isRelated = $entityManager->getRelation($account, 'contacts')->isRelated($contact);
        $this->assertTrue($isRelated);

        $entityManager->getRelation($account, 'contacts')->unrelate($contact);
        $isRelated = $entityManager->getRelation($account, 'contacts')->isRelated($contact);
        $this->assertFalse($isRelated);
    }

    public function testRelate2()
    {
        $entityManager = $this->getEntityManager();

        $account = $entityManager->getNewEntity('Account');
        $account->set('name', 'Test');
        $entityManager->saveEntity($account);

        $contact = $entityManager->getNewEntity('Contact');
        $contact->set('lastName', 'Test');
        $entityManager->saveEntity($contact);

        $entityManager->getRelation($contact, 'account')->relate($account);
        $isRelated = $entityManager->getRelation($contact, 'account')->isRelated($account);
        $this->assertTrue($isRelated);

        $contact = $entityManager->getEntityById('Contact', $contact->getId());

        $entityManager->getRelation($contact, 'account')->unrelate($account);

        $isRelated = $entityManager->getRelation($contact, 'account')->isRelated($account);
        $this->assertFalse($isRelated);
    }

    public function testRelate3WithEntityReFetching()
    {
        $entityManager = $this->getEntityManager();

        $account = $entityManager->getNewEntity('Account');
        $account->set('name', 'Test');
        $entityManager->saveEntity($account);

        $contact = $entityManager->getNewEntity('Contact');
        $contact->set('lastName', 'Test');
        $entityManager->saveEntity($contact);

        $entityManager->getRDBRepository('Contact')
            ->getRelation($contact, 'account')
            ->relate($account);

        $contact = $entityManager->getEntityById('Contact', $contact->get('id'));

        $isRelated = $entityManager->getRDBRepository('Contact')
            ->getRelation($contact, 'account')
            ->isRelated($account);

        $this->assertTrue($isRelated);

        $entityManager = $this->getEntityManager();

        $entityManager->getRDBRepository('Contact')
            ->getRelation($contact, 'account')
            ->unrelate($account);

        $contact = $entityManager->getEntityById('Contact', $contact->get('id'));

        $isRelated = $entityManager
            ->getRDBRepository('Contact')
            ->getRelation($contact, 'account')
            ->isRelated($account);

        $this->assertFalse($isRelated);
    }

    public function testRelate4()
    {
        $entityManager = $this->getEntityManager();

        $account = $entityManager->getNewEntity('Account');
        $account->set('name', 'Test');
        $entityManager->saveEntity($account);

        $task = $entityManager->getNewEntity('Task');
        $task->set('name', 'Test');
        $entityManager->saveEntity($task);


        $entityManager->getRelation($task, 'parent')->relate($account);
        $isRelated = $entityManager->getRelation($task, 'parent')->isRelated($account);
        $this->assertTrue($isRelated);

        $task = $entityManager->getEntityById('Task', $task->getId());

        $entityManager->getRelation($task, 'parent')->unrelate($account);
        $isRelated = $entityManager->getRelation($task, 'parent')->isRelated($account);
        $this->assertFalse($isRelated);
    }

    public function testRelateOneToOne1()
    {
        $em = $this->getEntityManager();

        $a1 = $em->createEntity('Account', [
            'name' => '1',
        ]);
        $a2 = $em->createEntity('Account', [
            'name' => '2',
        ]);
        $l1 = $em->createEntity('Lead', [
            'lastName' => '1',
        ]);
        $em->createEntity('Lead', [
            'lastName' => '2',
        ]);

        $em->getRelation($l1, 'createdAccount')->relate($a1);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a2);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a2, 'originalLead')->isRelated($l1);
        $this->assertFalse($isRelated);


        $em->getRelation($l1, 'createdAccount')->relate($a2);

        $isRelated = $em->getRelation($a2, 'originalLead')->isRelated($l1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a2);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertFalse($isRelated);

        $c = $em->getRDBRepository('Lead')->where(['createdAccountId' => $a1->getId()])->count();
        $this->assertEquals(0, $c);

        $c = $em->getRDBRepository('Lead')->where(['createdAccountId' => $a2->getId()])->count();
        $this->assertEquals(1, $c);
    }

    public function testRelateOneToOne2()
    {
        $em = $this->getEntityManager();

        $a1 = $em->createEntity('Account', [
            'name' => '1',
        ]);
        $a2 = $em->createEntity('Account', [
            'name' => '2',
        ]);
        $l1 = $em->createEntity('Lead', [
            'lastName' => '1',
        ]);

        $em->getRelation($a1, 'originalLead')->relate($l1);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a2);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a2, 'originalLead')->isRelated($l1);
        $this->assertFalse($isRelated);

        $em->getRelation($a2, 'originalLead')->relate($l1);

        $isRelated = $em->getRelation($a2, 'originalLead')->isRelated($l1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a2);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertFalse($isRelated);

        $c = $em->getRDBRepository('Lead')
            ->where(['createdAccountId' => $a1->getId()])
            ->count();

        $this->assertEquals(0, $c);

        $c = $em->getRDBRepository('Lead')
            ->where(['createdAccountId' => $a2->getId()])
            ->count();

        $this->assertEquals(1, $c);
    }

    public function testRelateOneToOne3()
    {
        $em = $this->getEntityManager();

        $a1 = $em->createEntity('Account', [
            'name' => '1',
        ]);
        $em->createEntity('Account', [
            'name' => '2',
        ]);
        $l1 = $em->createEntity('Lead', [
            'lastName' => '1',
        ]);
        $l2 = $em->createEntity('Lead', [
            'lastName' => '2',
        ]);

        $em->getRelation($l1, 'createdAccount')->relate($a1);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l2, 'createdAccount')->isRelated($a1);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l2);
        $this->assertFalse($isRelated);

        $em->getRelation($l2, 'createdAccount')->relate($a1);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l2);
        $this->assertTrue($isRelated);

        $isRelated = $em->getRelation($l2, 'createdAccount')->isRelated($a1);
        $this->assertTrue($isRelated);

        $l1 = $em->getEntityById('Lead', $l1->getId());

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);
        $this->assertFalse($isRelated);

        $isRelated = $em->getRelation($a1, 'originalLead')->isRelated($l1);
        $this->assertFalse($isRelated);

        $c = $em->getRDBRepository('Lead')
            ->where(['createdAccountId' => $a1->getId()])
            ->count();

        $this->assertEquals(1, $c);
    }

    public function testUnrelateOneToOne1()
    {
        $em = $this->getEntityManager();

        $a1 = $em->createEntity('Account', [
            'name' => '1',
        ]);
        $em->createEntity('Account', [
            'name' => '2',
        ]);
        $l1 = $em->createEntity('Lead', [
            'lastName' => '1',
        ]);
        $em->createEntity('Lead', [
            'lastName' => '2',
        ]);

        $em->getRelation($l1, 'createdAccount')->relate($a1);
        $em->getRelation($l1, 'createdAccount')->unrelate($a1);

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);

        $this->assertFalse($isRelated);

        $em->getRelation($l1, 'createdAccount')->relate($a1);
        $em->getRelation($a1, 'originalLead')->unrelate($l1);

        $l1 = $em->getEntityById('Lead', $l1->getId());

        $isRelated = $em->getRelation($l1, 'createdAccount')->isRelated($a1);

        $this->assertFalse($isRelated);
    }
}
