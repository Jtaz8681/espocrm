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

namespace tests\integration\Espo\Tools\Lock;

use Espo\Core\Action\ActionFactory;
use Espo\Core\Action\Data;
use Espo\Core\Action\Params;
use Espo\Core\Name\Field;
use Espo\Modules\Crm\Entities\Account;
use Espo\ORM\Exceptions\ValidationException;
use tests\integration\Core\BaseTestCase;

class LockTest extends BaseTestCase
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function testLock1(): void
    {
        $em = $this->getEntityManager();

        $account = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $account->setName('Test');
        $em->saveEntity($account);

        $account->set(Field::IS_LOCKED, true);
        $em->saveEntity($account);

        $this->expectException(ValidationException::class);

        $account->setName('Test 1');
        $em->saveEntity($account);

        //

        $actionFactory = $this->getInjectableFactory()->create(ActionFactory::class);

        $actionFactory->create('Unlock', Account::ENTITY_TYPE)->process(
            params: new Params(
                entityType: Account::ENTITY_TYPE,
                id: $account->getId(),
            ),
            data: Data::fromRaw((object) [])
        );


        $em->refreshEntity($account);

        $this->assertFalse($account->get(Field::IS_LOCKED));

        //

        $actionFactory->create('Lock', Account::ENTITY_TYPE)->process(
            params: new Params(
                entityType: Account::ENTITY_TYPE,
                id: $account->getId(),
            ),
            data: Data::fromRaw((object) [])
        );


        $em->refreshEntity($account);

        $this->assertTrue($account->get(Field::IS_LOCKED));
    }
}
