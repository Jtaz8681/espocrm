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

namespace tests\integration\Espo\Note;

use Espo\{
    Tools\Stream\Service as StreamService,
    ORM\EntityManager,
};

class AclTest extends \tests\integration\Core\BaseTestCase
{
    public function testProcessNoteAcl()
    {
        /* @var $em EntityManager*/
        $em = $this->getContainer()->get('entityManager');

        /* @var $streamService StreamService*/
        $streamService = $this->getContainer()->get('injectableFactory')->create(StreamService::class);

        $team1 = $em->createEntity('Team', [
            'name' => 'team-1',
        ]);

        $team2 = $em->createEntity('Team', [
            'name' => 'team-2',
        ]);

        $user1 = $em->createEntity('User', [
            'userName' => 'user-1',
            'lastName' => 'user-1',
        ]);

        $user2 = $em->createEntity('User', [
            'userName' => 'user-2',
            'lastName' => 'user-2',
        ]);

        $account = $em->createEntity('Account', [

        ]);

        // Opportunity

        $opportunity = $em->createEntity('Opportunity', [
            'assignedUserId' => $user1->getId(),
            'teamsIds' => [$team1->getId()],
            'accountId' => $account->getId(),
        ]);

        $streamService->noteCreate($opportunity);

        $note1 = $em
            ->getRDBRepository('Note')
            ->where([
                'type' => 'Create',
                'parentId' => $opportunity->getId(),
                'parentType' => $opportunity->getEntityType(),
            ])
            ->findOne();

        $this->assertEquals([$team1->getId()], $note1->getLinkMultipleIdList('teams'));
        $this->assertEquals([$user1->getId()], $note1->getLinkMultipleIdList('users'));

        $opportunity->set([
            'assignedUserId' => $user2->getId(),
            'teamsIds' => [$team2->getId()],
        ]);

        $em->saveEntity($opportunity);

        $note1 = $em->getEntityById('Note', $note1->getId());

        $this->assertEquals([$team2->getId()], $note1->getLinkMultipleIdList('teams'));
        $this->assertEquals([$user2->getId()], $note1->getLinkMultipleIdList('users'));

        // Meeting

        $meeting = $em->createEntity('Meeting', [
            'usersIds' => [$user1->getId()],
            'teamsIds' => [$team1->getId()],
            'parentId' => $account->getId(),
            'parentType' => $account->getEntityType(),
        ]);

        $streamService->noteRelate($meeting, $account);

        $note2 = $em
            ->getRDBRepository('Note')
            ->where([
                'type' => 'Relate',
                'relatedId' => $meeting->getId(),
                'relatedType' => $meeting->getEntityType(),
            ])
            ->findOne();

        $this->assertEquals([$team1->getId()], $note2->getLinkMultipleIdList('teams'));
        $this->assertEquals([$user1->getId()], $note2->getLinkMultipleIdList('users'));

        $meeting->set([
            'usersIds' => [$user2->getId()],
            'teamsIds' => [$team2->getId()],
        ]);

        $em->saveEntity($meeting);

        $note2 = $em->getEntityById('Note', $note2->getId());

        $this->assertEquals([$team2->getId()], $note2->getLinkMultipleIdList('teams'));
        $this->assertEquals([$user2->getId()], $note2->getLinkMultipleIdList('users'));
    }
}
