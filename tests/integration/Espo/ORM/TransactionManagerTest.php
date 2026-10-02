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

use tests\integration\Core\BaseTestCase;

class TransactionManagerTest extends BaseTestCase
{
    public function testOne()
    {
        $em = $this->getEntityManager();

        $tm = $em->getTransactionManager();

        $tm->start();

        $account = $em->createEntity('Account', [
            'name' => 'test',
        ]);

        $this->assertNotNull($account);

        $id = $account->getId();

        $tm->commit();

        $account = $em->getEntityById('Account', $id);

        $this->assertNotNull($account);
    }

    public function testRollbackOne()
    {
        $em = $this->getEntityManager();

        $tm = $em->getTransactionManager();

        $tm->start();

        $account = $em->createEntity('Account', [
            'name' => 'test',
        ]);

        $this->assertNotNull($account);

        $id = $account->getId();

        $tm->rollback();

        $account = $em->getEntityById('Account', $id);

        $this->assertNull($account);
    }

    public function testRollbackNested()
    {
        $em = $this->getEntityManager();

        $tm = $em->getTransactionManager();

        $tm->start();

        $account1 = $em->createEntity('Account', [
            'name' => 'test1',
        ]);

        $id1 = $account1->getId();

        $tm->start();

        $account2 = $em->createEntity('Account', [
            'name' => 'test2',
        ]);

        $id2 = $account2->getId();

        $tm->rollback();

        $tm->commit();

        $account1 = $em->getEntityById('Account', $id1);
        $account2 = $em->getEntityById('Account', $id2);

        $this->assertNotNull($account1);
        $this->assertNull($account2);
    }

    public function testRunCommit()
    {
        $em = $this->getEntityManager();

        $tm = $em->getTransactionManager();

        $account = $em->createEntity('Account', [
            'name' => 'test',
        ]);

        $id = $account->getId();

        $tm->run(
            function () use ($em, $id){
                $account = $em->getEntityById('Account', $id);
                $account->set('name', 'test-1');
                $em->saveEntity($account);
            }
        );

        $account = $em->getEntityById('Account', $id);

        $this->assertNotNull($account);
        $this->assertEquals('test-1', $account->get('name'));
    }

    public function testRunRollback()
    {
        $em = $this->getEntityManager();

        $tm = $em->getTransactionManager();

        $account = $em->createEntity('Account', [
            'name' => 'test',
        ]);

        $id = $account->getId();

        try {
            $tm->run(
                function () use ($em, $id){
                    $account = $em->getEntity('Account', $id);
                    $account->set('name', 'test-1');
                    $em->saveEntity($account);

                    throw new \Exception();
                }
            );
        } catch (\Exception) {}

        $account = $em->getEntityById('Account', $id);

        $this->assertNotNull($account);
        $this->assertEquals('test', $account->get('name'));
    }
}
