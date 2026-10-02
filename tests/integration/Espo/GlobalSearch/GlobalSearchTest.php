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

namespace tests\integration\Espo\GlobalSearch;

use Espo\Tools\GlobalSearch\Service;
use tests\integration\Core\BaseTestCase;

class GlobalSearchTest extends BaseTestCase
{
    public function testSearch1(): void
    {

        $em = $this->getEntityManager();

        $team = $em->createEntity('Team', [
            'name' => 'test',
        ]);

        $contact = $em->createEntity('Contact', [
            'lastName' => '1',
            'teamsIds' => [$team->getId()],
        ]);

        $account = $em->createEntity('Account', [
            'name' => '1',
            'teamsIds' => [$team->getId()],
        ]);
        $account = $em->createEntity('Account', [
            'name' => '2',
            'teamsIds' => [$team->getId()],
        ]);
        $account = $em->createEntity('Account', [
            'name' => '1',
        ]);

        $this->createUser([
            'userName' => 'tester',
            'teamsIds' => [$team->getId()],
        ], [
            'data' => [
                'Account' => [
                    'create' => 'no',
                    'read' => 'team',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'no',
                ],
                'Contact' => [
                    'create' => 'no',
                    'read' => 'team',
                    'edit' => 'no',
                    'delete' => 'no',
                    'stream' => 'no',
                ],
            ],
        ]);

        $this->authenticate('tester');

        $service = $this->getInjectableFactory()->create(Service::class);

        $result = $service->find('1', 0, 10);

        $this->assertEquals(2, count($result->getCollection()));
    }
}
