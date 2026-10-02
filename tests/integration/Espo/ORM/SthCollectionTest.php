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
use Espo\ORM\SthCollection;
use tests\integration\Core\BaseTestCase;

class SthCollectionTest extends BaseTestCase
{
    public function test1()
    {
        $em = $this->getEntityManager();

        $em->createEntity('Account', [
            'name' => 'test-1',
        ]);

        $em->createEntity('Account', [
            'name' => 'test-2',
        ]);
        $em->createEntity('Account', [
            'name' => 'test-3',
        ]);

        $query = $em->getQueryBuilder()
            ->select()
            ->from('Account')
            ->limit(0, 2)
            ->order('name')
            ->build();

        $collection = $em->getCollectionFactory()->createFromQuery($query);

        $count = 0;
        $list = [];

        foreach ($collection as $e) {
            $count++;
            $list[] = $e;
        }

        $this->assertEquals(2, $count);

        $this->assertEquals('test-1', $list[0]->get('name'));

        $array = $collection->getValueMapList();

        $this->assertEquals('test-2', $array[1]->name);
    }

    public function test2(): void
    {
        $em = $this->getEntityManager();

        $em->createEntity('Account', [
            'name' => 'test-2',
        ]);
        $em->createEntity('Account', [
            'name' => 'test-3',
        ]);

        $query = $em->getQueryBuilder()
            ->select()
            ->from('Account')
            ->limit(0, 2)
            ->order('name')
            ->build();

        $collection = $em->getCollectionFactory()->createFromQuery($query);

        $count = 0;

        foreach ($collection as $ignored) {
            $count++;
        }

        $this->assertEquals(2, $count);
    }

    public function testFind1()
    {
        $em = $this->getEntityManager();

        $em->createEntity('Account', [
            'name' => 'test-1',
        ]);
        $em->createEntity('Account', [
            'name' => 'test-2',
        ]);
        $em->createEntity('Account', [
            'name' => 'test-3',
        ]);

        $query = $em->getQueryBuilder()
            ->select()
            ->from('Account')
            ->where(['name' => 'test-1'])
            ->build();

        $collection = $em->getRDBRepository('Account')
            ->clone($query)
            ->sth()
            ->find();

        $this->assertEquals(SthCollection::class, get_class($collection));

        $count = 0;

        foreach ($collection as $e) {
            $count++;
        }

        $this->assertEquals(1, $count);
    }

    public function testFindRelatedOneToMany()
    {
        $em = $this->getEntityManager();

        $account = $em->createEntity('Account', [
            'name' => 'test-1',
        ]);
        $em->createEntity('Opportunity', [
            'name' => 'o-1',
            'accountId' => $account->getId(),
        ]);
        $em->createEntity('Opportunity', [
            'name' => 'o-2',
            'accountId' => $account->getId(),
        ]);

        $query = $em->getQueryBuilder()
            ->select()
            ->from('Opportunity')
            ->order('name')
            ->build();

        $collection = $em
            ->getRelation($account, 'opportunities')
            ->clone($query)
            ->sth()
            ->find();

        $this->assertEquals(SthCollection::class, get_class($collection));

        $count = 0;

        foreach ($collection as $e) {
            $count++;
        }

        $this->assertEquals(2, $count);

        $array = $collection->getValueMapList();

        $this->assertEquals('o-1', $array[0]->name);
    }

    public function testFindRelatedManyToMany()
    {
        $em = $this->getEntityManager();

        $contact = $em->createEntity('Contact', [
            'lastName' => 'test-1',
        ]);
        $em->createEntity('Opportunity', [
            'name' => 'o-1',
            'contactsIds' => [$contact->getId()],
        ]);
        $em->createEntity('Opportunity', [
            'name' => 'o-2',
            'contactsIds' => [$contact->getId()],
        ]);


        $query = $em->getQueryBuilder()
            ->select()
            ->from('Opportunity')
            ->build();

        $collection = $em->getRepository('Contact')->getRelation($contact, 'opportunities')
            ->clone($query)
            ->sth()
            ->find();

        $this->assertEquals(SthCollection::class, get_class($collection));

        $count = 0;

        foreach ($collection as $e) {
            $count++;
        }

        $this->assertEquals(2, $count);
    }

    public function testMethods(): void
    {
        $e1 = $this->getEntityManager()->createEntity(Account::ENTITY_TYPE, ['name' => '1']);
        $e2 = $this->getEntityManager()->createEntity(Account::ENTITY_TYPE, ['name' => '2']);
        $e3 = $this->getEntityManager()->createEntity(Account::ENTITY_TYPE, ['name' => '3']);
        $e4 = $this->getEntityManager()->createEntity(Account::ENTITY_TYPE, ['name' => '4']);

        $collection = $this->getEntityManager()
            ->getRDBRepositoryByClass(Account::class)
            ->sth()
            ->order('name')
            ->find();

        $filtered = $collection->filter(function ($e) use ($e2, $e3) {
            return $e->getId() !== $e2->getId() && $e->getId() !== $e3->getId();
        });

        $this->assertEquals([$e1->getId(), $e4->getId()], array_map(fn ($it) => $it->getId(), [...$filtered]));
        $this->assertEquals($collection->getEntityType(), $filtered->getEntityType());
    }
}
