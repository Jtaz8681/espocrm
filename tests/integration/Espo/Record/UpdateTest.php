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

use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use tests\integration\Core\BaseTestCase;

class UpdateTest extends BaseTestCase
{
    public function testUpdateForeign(): void
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Contact::class);

        $em = $this->getEntityManager();

        $account1 = $em->createEntity(Account::ENTITY_TYPE, ['type' => Account::TYPE_CUSTOMER]);
        $account2 = $em->createEntity(Account::ENTITY_TYPE, ['type' => Account::TYPE_PARTNER]);
        $contact = $em->createEntity(Contact::ENTITY_TYPE, ['accountId' => $account1->getId()]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $contact = $service->update(
            $contact->getId(),
            (object) ['accountId' => $account2->getId()],
            UpdateParams::create()
        );

        $this->assertEquals(Account::TYPE_PARTNER, $contact->get('accountType'));
    }
}
