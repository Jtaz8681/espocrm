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

use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use tests\integration\Core\BaseTestCase;

class RepositoryTest extends BaseTestCase
{
    public function testModifiedBy(): void
    {
        $user1 = $this->createUser('test-1');
        $user2 = $this->createUser('test-2');

        $this->authenticate('test-1');

        $em = $this->getEntityManager();

        $account = $em->createEntity(Account::ENTITY_TYPE, ['name' => '1']);

        $this->assertEquals($user1->getId(), $account->get('createdById'));

        $this->authenticate('test-2');


        $em = $this->getEntityManager();

        $account = $em->getEntityById(Account::ENTITY_TYPE, $account->getId());
        $account->set('name', '2');
        $em->saveEntity($account);

        $this->assertEquals($user2->getId(), $account->get('modifiedById'));

        $this->authenticate('test-1');

        $em = $this->getEntityManager();

        $account = $em->getEntityById(Account::ENTITY_TYPE, $account->getId());
        $account->set('name', '2');
        $em->saveEntity($account);

        $this->assertEquals($user2->getId(), $account->get('modifiedById'));
    }

    public function testIsRelated(): void
    {
        $em = $this->getEntityManager();

        $acc1 = $em->createEntity(Account::ENTITY_TYPE, []);
        $acc2 = $em->createEntity(Account::ENTITY_TYPE, []);

        $opp1 = $em->createEntity(Opportunity::ENTITY_TYPE, []);

        $contact1 = $em->createEntity(Contact::ENTITY_TYPE, []);
        $contact2 = $em->createEntity(Contact::ENTITY_TYPE, []);

        $oppRepo = $em->getRDBRepositoryByClass(Opportunity::class);

        $oppRepo->getRelation($opp1, 'account')->relate($acc1);
        $oppRepo->getRelation($opp1, 'contacts')->relate($contact1);

        $this->assertTrue(
            $oppRepo->getRelation($opp1, 'account')->isRelatedById($acc1->getId())
        );

        $this->assertTrue(
            $oppRepo->getRelation($opp1, 'account')->isRelated($acc1)
        );

        $this->assertFalse(
            $oppRepo->getRelation($opp1, 'account')->isRelatedById($acc2->getId())
        );

        $this->assertFalse(
            $oppRepo->getRelation($opp1, 'account')->isRelated($acc2)
        );

        $this->assertTrue(
            $oppRepo->getRelation($opp1, 'contacts')->isRelatedById($contact1->getId())
        );

        $this->assertTrue(
            $oppRepo->getRelation($opp1, 'contacts')->isRelated($contact1)
        );

        $this->assertFalse(
            $oppRepo->getRelation($opp1, 'contacts')->isRelatedById($contact2->getId())
        );

        $this->assertFalse(
            $oppRepo->getRelation($opp1, 'contacts')->isRelated($contact2)
        );
    }
}
