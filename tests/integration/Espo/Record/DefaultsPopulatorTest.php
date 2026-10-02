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

use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\Portal;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Modules\Crm\Entities\Contact;
use tests\integration\Core\BaseTestCase;

class DefaultsPopulatorTest extends BaseTestCase
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Conflict
     */
    public function testPortal(): void
    {
        $em = $this->getEntityManager();

        $account = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $em->saveEntity($account);

        $contact = $em->getRDBRepositoryByClass(Contact::class)->getNew();
        $contact->setAccount($account);
        $em->saveEntity($contact);

        $portal = $em->getRDBRepositoryByClass(Portal::class)->getNew();
        $em->saveEntity($portal);

        $this->createUser([
            'userName' => 'tester',
            'portalsIds' => [$portal->getId()],
            'contactId' => $contact->getId(),
            'accountsIds' => [$account->getId()],
        ], [
            'data' => [
                'Case' => [
                    'create' => Table::LEVEL_YES,
                    'read' => 'contact',
                ],
            ],
        ], true);

        $this->auth(userName: 'tester', portalId: $portal->getId());
        $this->reCreateApplication(reuse: true);

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->getByClass(CaseObj::class);

        $result = $service->create((object) [
            'name' => 'Test',
        ], CreateParams::create());

        $this->assertEquals($contact->getId(), $result->getEntity()->getContact()?->getId());
        $this->assertEquals($account->getId(), $result->getEntity()->getAccount()?->getId());
    }
}
