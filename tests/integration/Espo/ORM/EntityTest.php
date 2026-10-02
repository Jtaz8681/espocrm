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

use Espo\Core\Name\Field;
use Espo\Entities\Team;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class EntityTest extends BaseTestCase
{
    public function testLinkMultiple(): void
    {
        $em = $this->getEntityManager();

        $team1 = $em->createEntity(Team::ENTITY_TYPE);
        $team2 = $em->createEntity(Team::ENTITY_TYPE);
        $team3 = $em->createEntity(Team::ENTITY_TYPE);

        $account = $em->createEntity(Account::ENTITY_TYPE, [
            'teamsIds' => [
                $team1->getId(),
                $team2->getId(),
            ],
        ]);

        $account = $em->getRDBRepositoryByClass(Account::class)->getById($account->getId());

        $set = $account->getLinkMultipleIdList(Field::TEAMS);
        $expected = [$team1->getId(), $team2->getId()];

        $this->assertTrue(
            array_diff($set, $expected) === array_diff($expected, $set)
        );

        //

        $account = $em->getRDBRepositoryByClass(Account::class)->getById($account->getId());

        $account->addLinkMultipleId(Field::TEAMS, $team3->getId());

        $set = $account->getFetchedLinkMultipleIdList(Field::TEAMS);
        $expected = [$team1->getId(), $team2->getId()];

        $this->assertTrue(
            array_diff($set, $expected) === array_diff($expected, $set)
        );

        $set = $account->getLinkMultipleIdList(Field::TEAMS);
        $expected = [$team1->getId(), $team2->getId(), $team3->getId()];

        $this->assertTrue(
            array_diff($set, $expected) === array_diff($expected, $set)
        );

        //

        $account = $em->getRDBRepositoryByClass(Account::class)->getById($account->getId());

        $set = $account->getFetchedLinkMultipleIdList(Field::TEAMS);
        $expected = [$team1->getId(), $team2->getId()];

        $this->assertTrue(
            array_diff($set, $expected) === array_diff($expected, $set)
        );
    }
}
