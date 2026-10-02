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

use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\EntityCollection;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Repository\Option\SaveOption;
use tests\integration\Core\BaseTestCase;
use tests\integration\testClasses\Entities\Account as AccountExtended;

class RelationsTest extends BaseTestCase
{
    public function testGetOne(): void
    {
        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', Opportunity::ENTITY_TYPE, [
            'links' => [
                'account' => [
                    'deferredLoad' => true,
                ],
            ],
        ]);
        $metadata->save();

        $this->authenticate();

        $em = $this->getEntityManager();

        $opp = $em->getNewEntity(Opportunity::ENTITY_TYPE);
        $this->assertInstanceOf(Opportunity::class, $opp);
        $this->assertNull($opp->getAccount());

        /** @var Account $account */
        $account = $em->createEntity(Account::ENTITY_TYPE, ['name' => 'Account 1']);

        $opp = $em->createEntity(Opportunity::ENTITY_TYPE, [
            'accountId' => $account->getId(),
        ]);

        $this->assertInstanceOf(Opportunity::class, $opp);

        $account1 = $opp->getAccount();

        $this->assertInstanceOf(Account::class, $account1);
        $this->assertEquals($account->getId(), $account1->getId());

        $em->refreshEntity($opp);

        $account2 = $opp->getAccount();

        $this->assertInstanceOf(Account::class, $account2);
        $this->assertNotSame($account1, $account2);
        $this->assertEquals($account->getId(), $account2->getId());
        $this->assertEquals($account->getName(), $account2->getName());

        $em->saveEntity($opp);

        $account3 = $opp->getAccount();

        $this->assertInstanceOf(Account::class, $account3);
        $this->assertNotSame($account1, $account3);
        $this->assertEquals($account->getId(), $account3->getId());

        // Soft-deleted.

        $em->removeEntity($account3);
        $em->refreshEntity($opp);

        $this->assertNull($opp->getAccount());
    }

    public function testGetMany(): void
    {
        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', Account::ENTITY_TYPE, [
            'entityClassName' => AccountExtended::class,
        ]);
        $metadata->save();

        $this->authenticate();

        $em = $this->getEntityManager();

        $account = $em->getNewEntity(Account::ENTITY_TYPE);
        $this->assertInstanceOf(AccountExtended::class, $account);
        $this->assertInstanceOf(EntityCollection::class, $account->getRelatedOpportunities());

        $account = $em->createEntity(Account::ENTITY_TYPE);
        $this->assertInstanceOf(AccountExtended::class, $account);

        $em->createEntity(Opportunity::ENTITY_TYPE, ['accountId' => $account->getId()]);
        $em->createEntity(Opportunity::ENTITY_TYPE, ['accountId' => $account->getId()]);
        $em->createEntity(Opportunity::ENTITY_TYPE);

        $collection = $account->getRelatedOpportunities();

        $this->assertInstanceOf(EntityCollection::class, $collection);
        $items = iterator_to_array($collection);

        $this->assertCount(2, $items);
    }

    public function testSet(): void
    {
        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', Account::ENTITY_TYPE, [
            'entityClassName' => AccountExtended::class,
        ]);
        $metadata->save();

        $this->authenticate(null);

        //$this->reCreateApplication();

        $em = $this->getEntityManager();

        /** @var Account $account */
        $account = $em->createEntity(Account::ENTITY_TYPE, ['name' => 'Account 1']);

        // belongsTo

        $opp = $em->getRDBRepositoryByClass(Opportunity::class)->getNew();
        $opp->setAccount($account);
        $em->saveEntity($opp, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($opp);

        $this->assertEquals($account->getId(), $opp->getAccount()->getId());
        $this->assertEquals($account->getName(), $opp->getAccount()->getName());

        $opp->setAccount(null);
        $em->saveEntity($opp, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($opp);

        $this->assertNull($opp->getAccount());

        // belongsToParent

        $task = $em->getRDBRepositoryByClass(Task::class)->getNew();
        $task->setParent($account);
        $em->saveEntity($task, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($task);

        $this->assertEquals($account->getId(), $task->get('parentId'));
        $this->assertEquals($account->getEntityType(), $task->get('parentType'));
        $this->assertEquals($account->getId(), $task->getParent()->getId());
        $this->assertEquals($account->getName(), $task->getParent()->get('name'));

        $task = $em->getRDBRepositoryByClass(Task::class)->getNew();
        $task->setParent(null);
        $em->saveEntity($task, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($task);

        $this->assertNull($task->get('parentId'));
        $this->assertNull($task->get('parentType'));

        // belongsTo hasOne

        $lead1 = $em->getRDBRepositoryByClass(Lead::class)->getNew();
        $lead1->setCreatedAccount($account);
        $em->saveEntity($lead1, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($lead1);

        $this->assertEquals($account->getId(), $lead1->get('createdAccountId'));

        $lead2 = $em->getRDBRepositoryByClass(Lead::class)->getNew();
        $lead2->setCreatedAccount($account);
        $em->saveEntity($lead2, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($lead2);

        $this->assertEquals($account->getId(), $lead2->get('createdAccountId'));

        $em->refreshEntity($lead1);

        $this->assertEquals(null, $lead1->get('createdAccountId'));

        $lead2->setCreatedAccount(null);
        $em->saveEntity($lead2);

        // hasOne

        $em->refreshEntity($account);

        $this->assertInstanceOf(AccountExtended::class, $account);

        $account->setRelatedOriginalLead($lead1);
        $em->saveEntity($account, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($lead1);

        $this->assertEquals($account->getId(), $lead1->get('createdAccountId'));

        $account->setRelatedOriginalLead($lead2);
        $em->saveEntity($account, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($lead2);

        $this->assertEquals($account->getId(), $lead2->get('createdAccountId'));

        $em->refreshEntity($lead1);

        $this->assertEquals(null, $lead1->get('createdAccountId'));

        $account->setRelatedOriginalLead(null);
        $em->saveEntity($account, [SaveOption::SKIP_ALL => true]);
        $em->refreshEntity($lead2);

        $this->assertEquals(null, $lead2->get('createdAccountId'));
    }

    public function testOrder(): void
    {
        $em = $this->getEntityManager();

        $account1 = $em->createEntity(Account::ENTITY_TYPE, ['name' => 'a1']);
        $account2 = $em->createEntity(Account::ENTITY_TYPE, ['name' => 'a2']);

        $contact = $em->createEntity(Contact::ENTITY_TYPE);

        $this->assertInstanceOf(Contact::class, $contact);

        $em->getRelation($contact, 'accounts')->relate($account1);
        $em->getRelation($contact, 'accounts')->relate($account2);

        //

        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', Contact::ENTITY_TYPE, [
            'links' => [
                'accounts' => [
                    RelationParam::ORDER_BY => 'name',
                    RelationParam::ORDER => 'desc',
                ]
            ]
        ]);
        $metadata->save();

        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $em = $this->getEntityManager();

        $contact = $em->getRDBRepositoryByClass(Contact::class)->getById($contact->getId());
        $this->assertNotNull($contact);

        $accounts = $contact->getAccounts();

        $this->assertCount(2, $accounts);
        $this->assertEquals('a2', $accounts[0]->getName());
        $this->assertEquals('a1', $accounts[1]->getName());

        //

        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', Contact::ENTITY_TYPE, [
            'links' => [
                'accounts' => [
                    RelationParam::ORDER_BY => 'name',
                    RelationParam::ORDER => 'asc',
                ]
            ]
        ]);
        $metadata->save();

        $this->getDataManager()->clearCache();
        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $em = $this->getEntityManager();

        $contact = $em->getRDBRepositoryByClass(Contact::class)->getById($contact->getId());
        $this->assertNotNull($contact);

        $accounts = $contact->getAccounts();

        $this->assertCount(2, $accounts);
        $this->assertEquals('a1', $accounts[0]->getName());
        $this->assertEquals('a2', $accounts[1]->getName());
    }
}
