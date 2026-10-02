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

namespace tests\integration\Espo\Core\ORM;

use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use tests\integration\Core\BaseTestCase;

class CoreEntityTest extends BaseTestCase
{
    public function testLinkMultiple(): void
    {
        $contact1 = $this->getEntityManager()->createEntity(Contact::ENTITY_TYPE, [
            'lastName' => 'Test 1',
        ]);

        $contact2 = $this->getEntityManager()->createEntity(Contact::ENTITY_TYPE, [
            'lastName' => 'Test 2',
        ]);

        $opp = $this->getEntityManager()->createEntity(Opportunity::ENTITY_TYPE, [
            'name' => 'Test',
            'contactsIds' => [$contact1->getId()],
            'contactsColumns' => [
                $contact1->getId() => ['role' => 'Evaluator']
            ],
        ]);

        $opp = $this->getEntityManager()
            ->getRDBRepositoryByClass(Opportunity::class)
            ->getById($opp->getId());

        $this->assertTrue(in_array($contact1->getId(), $opp->getLinkMultipleIdList('contacts')));
        $this->assertEquals('Evaluator', $opp->getLinkMultipleColumn('contacts', 'role', $contact1->getId()));

        $opp->addLinkMultipleId('contacts', $contact2->getId());
        $opp->setLinkMultipleColumn('contacts', 'role', $contact2->getId(), 'Decision Maker');

        $this->assertTrue($opp->hasLinkMultipleId('contacts', $contact2->getId()));
        $this->assertEquals('Decision Maker', $opp->getLinkMultipleColumn('contacts', 'role', $contact2->getId()));

        // Rewrites set values.
        $opp->loadLinkMultipleField('contacts');

        $this->assertFalse($opp->hasLinkMultipleId('contacts', $contact2->getId()));

        $this->assertTrue(in_array($contact1->getId(), $opp->getFetched('contactsIds')));
        $this->assertFalse(in_array($contact2->getId(), $opp->getFetched('contactsIds')));
    }
}
