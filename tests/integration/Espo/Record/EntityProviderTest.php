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

namespace tests\integration\Espo\Record;

use Espo\Core\Record\EntityProvider;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class EntityProviderTest extends BaseTestCase
{
    public function testGet(): void
    {
        $account = $this->getEntityManager()->createEntity(Account::ENTITY_TYPE);
        $id = $account->getId();

        $provider = $this->getInjectableFactory()->create(EntityProvider::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $account = $provider->getByClass(Account::class, $account->getId());

        $this->assertEquals($id, $account->getId());
    }
}
