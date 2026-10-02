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

namespace tests\integration\Espo\Stream;

use Espo\Core\Acl\Table;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\Note;
use Espo\Modules\Crm\Entities\Account;
use Espo\Tools\Stream\Api\DeleteMyReactions;
use Espo\Tools\Stream\Api\PostMyReactions;
use Espo\Tools\Stream\MassNotePreparator;
use tests\integration\Core\BaseTestCase;

class ReactionsTest extends BaseTestCase
{
    private const REACTION_LIKE = 'Like';

    public function testReactions(): void
    {
        $userTest = $this->createUser('test', [
            'data' => [
                Account::ENTITY_TYPE => [
                    'create' => Table::LEVEL_NO,
                    'read' => Table::LEVEL_ALL,
                    'stream' => Table::LEVEL_OWN,
                ],
            ]
        ]);

        $em = $this->getEntityManager();

        $account1 = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $account1->setAssignedUser($userTest);
        $em->saveEntity($account1);

        $account2 = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $em->saveEntity($account2);

        $note1 = $this->getEntityManager()->getRDBRepositoryByClass(Note::class)->getNew();
        $note1
            ->setType(Note::TYPE_POST)
            ->setParent($account1)
            ->setPost('Test');
        $em->saveEntity($note1);

        $note2 = $this->getEntityManager()->getRDBRepositoryByClass(Note::class)->getNew();
        $note2
            ->setType(Note::TYPE_POST)
            ->setParent($account2)
            ->setPost('Test');
        $em->saveEntity($note2);

        $this->authenticate('test');

        // Has access.

        $request = $this->createMock(Request::class);

        $request->expects($this->any())
            ->method('getRouteParam')
            ->willReturnMap([
                ['id', $note1->getId()],
                ['type', self::REACTION_LIKE],
            ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getInjectableFactory()
            ->create(PostMyReactions::class)
            ->process($request);

        $this->getInjectableFactory()->create(MassNotePreparator::class)->prepare([$note1]);

        $this->assertEquals([self::REACTION_LIKE], $note1->get('myReactions'));
        $this->assertEquals(1, $note1->get('reactionCounts')->{self::REACTION_LIKE});

        // Un-react.

        $request = $this->createMock(Request::class);

        $request->expects($this->any())
            ->method('getRouteParam')
            ->willReturnMap([
                ['id', $note1->getId()],
                ['type', self::REACTION_LIKE],
            ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getInjectableFactory()
            ->create(DeleteMyReactions::class)
            ->process($request);

        $this->getInjectableFactory()->create(MassNotePreparator::class)->prepare([$note1]);

        $this->assertEquals([], $note1->get('myReactions'));
        $this->assertEquals(0, $note1->get('reactionCounts')->{self::REACTION_LIKE} ?? 0);

        // No access to note.

        $isThrown = false;

        try {
            $request = $this->createMock(Request::class);

            $request->expects($this->any())
                ->method('getRouteParam')
                ->willReturnMap([
                    ['id', $note2->getId()],
                    ['type', self::REACTION_LIKE],
                ]);

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->getInjectableFactory()
                ->create(PostMyReactions::class)
                ->process($request);
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);

        // No access to note to un-react.

        $isThrown = false;

        try {
            $request = $this->createMock(Request::class);

            $request->expects($this->any())
                ->method('getRouteParam')
                ->willReturnMap([
                    ['id', $note2->getId()],
                    ['type', self::REACTION_LIKE],
                ]);

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->getInjectableFactory()
                ->create(DeleteMyReactions::class)
                ->process($request);
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);

        // Not allowed reaction.

        $isThrown = false;

        try {
            $request = $this->createMock(Request::class);

            $request->expects($this->any())
                ->method('getRouteParam')
                ->willReturnMap([
                    ['id', $note1->getId()],
                    ['type', 'Smile'],
                ]);

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->getInjectableFactory()
                ->create(PostMyReactions::class)
                ->process($request);
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);
    }
}
