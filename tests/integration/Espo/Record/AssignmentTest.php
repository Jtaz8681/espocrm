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
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Lead;
use tests\integration\Core\BaseTestCase;

class AssignmentTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testAssignmentSelf(): void
    {
        $team1 = $this->getEntityManager()->createEntity(Team::ENTITY_TYPE);

        $user1 = $this->createUser([
            'userName' => 'test1',
            'defaultTeamId' => $team1->getId(),
            'teamsIds' => [$team1->getId()],
        ], [
            'data' => [
                Lead::ENTITY_TYPE => [
                    'create' => Table::LEVEL_YES,
                    'read' => Table::LEVEL_OWN,
                    'edit' => Table::LEVEL_OWN,
                    'delete' => Table::LEVEL_OWN,
                ],
            ],
            'assignmentPermission' => Table::LEVEL_NO,
        ]);

        $this->authenticate('test1');

        $lead1 = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Lead::class)
            ->create((object) [
                'lastName' => 'Test 1',
            ])->getEntity();

        $this->assertEquals($user1->getId(), $lead1->getAssignedUser()?->getId());
        $this->assertEquals([$team1->getId()], $lead1->getLinkMultipleIdList('teams'));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testAssignment1(): void
    {
        $team = $this->getEntityManager()->createEntity(Team::ENTITY_TYPE);

        $this->createUser([
            'userName' => 'test1',
            'defaultTeamId' => $team->getId(),
            'teamsIds' => [$team->getId()],
        ], [
            'data' => [
                Lead::ENTITY_TYPE => [
                    'create' => Table::LEVEL_YES,
                    'read' => Table::LEVEL_OWN,
                    'edit' => Table::LEVEL_OWN,
                    'delete' => Table::LEVEL_OWN,
                ],
                User::ENTITY_TYPE => [
                    'read' => Table::LEVEL_TEAM,
                ],
            ],
            'assignmentPermission' => Table::LEVEL_TEAM,
        ]);

        $user2 = $this->createUser([
            'userName' => 'test2',
            'defaultTeamId' => $team->getId(),
            'teamsIds' => [$team->getId()],
        ], [
            'data' => [
                Lead::ENTITY_TYPE => [
                    'create' => Table::LEVEL_YES,
                    'read' => Table::LEVEL_OWN,
                    'edit' => Table::LEVEL_OWN,
                    'delete' => Table::LEVEL_OWN,
                ],
            ],
        ]);

        $this->authenticate('test1');

        $lead = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Lead::class)
            ->create((object) [
                'lastName' => 'Test 1',
                'assignedUserId' => $user2->getId(),
            ])->getEntity();

        $this->assertEquals($user2->getId(), $lead->getAssignedUser()?->getId());
    }
}
