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

use Espo\Entities\Team;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class EntityManagerTest extends BaseTestCase
{
    public function testRefreshEntity(): void
    {
        $em = $this->getEntityManager();

        $team1 = $em->createEntity(Team::ENTITY_TYPE);
        $team2 = $em->createEntity(Team::ENTITY_TYPE);

        /** @var Account $account */
        $account = $em->createEntity(Account::ENTITY_TYPE, [
            'name' => 'Test',
            'teamsIds' => [$team1->getId()],
        ]);

        $account->set('name', 'Hello');

        $em->getRelation($account, 'teams')->relateById($team2->getId());

        $em->refreshEntity($account);

        $this->assertEquals('Test', $account->get('name'));
        $this->assertFalse($account->isAttributeChanged('name'));
        $this->assertEqualsCanonicalizing([$team1->getId(), $team2->getId()], $account->getLinkMultipleIdList('teams'));
    }
}
