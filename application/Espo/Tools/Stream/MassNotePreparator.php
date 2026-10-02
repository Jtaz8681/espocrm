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

namespace Espo\Tools\Stream;

use Espo\Core\Utils\Config;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\Entities\UserReaction;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\SelectBuilder;

/**
 * @internal
 */
class MassNotePreparator
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Config $config,
    ) {}

    /**
     * @param iterable<Note> $notes
     */
    public function prepare(iterable $notes): void
    {
        if ($this->noAvailableReactions()) {
            return;
        }

        $ids = $this->getPostIds($notes);

        $this->prepareMyReactions($ids, $notes);
        $this->prepareReactionCounts($ids, $notes);
    }

    /**
     * @param iterable<Note> $notes
     * @return string[]
     */
    private function getPostIds(iterable $notes): array
    {
        $ids = [];

        foreach ($notes as $note) {
            if ($note->getType() !== Note::TYPE_POST) {
                continue;
            }

            $ids[] = $note->getId();
        }

        return $ids;
    }

    /**
     * @param string[] $ids
     * @param iterable<Note> $notes
     */
    private function prepareMyReactions(array $ids, iterable $notes): void
    {
        $myUserReactionCollection = $this->entityManager
            ->getRDBRepositoryByClass(UserReaction::class)
            ->where([
                'userId' => $this->user->getId(),
                'parentType' => Note::ENTITY_TYPE,
                'parentId' => $ids,
            ])
            ->find();

        /** @var UserReaction[] $myUserReactions */
        $myUserReactions = iterator_to_array($myUserReactionCollection);

        foreach ($notes as $note) {
            $noteMyReactions = [];

            foreach ($myUserReactions as $reaction) {
                if ($reaction->getParent()->getId() !== $note->getId()) {
                    continue;
                }

                $noteMyReactions[] = $reaction->getType();
            }

            $note->set('myReactions', $noteMyReactions);
        }
    }

    /**
     * @param string[] $ids
     * @param iterable<Note> $notes
     */
    private function prepareReactionCounts(array $ids, iterable $notes): void
    {
        $query = SelectBuilder::create()
            ->from(UserReaction::ENTITY_TYPE)
            ->select([
                Selection::create(Expression::count(Expression::column('id')), 'count'),
                'parentId',
                'type',
            ])
            ->where([
                'parentType' => Note::ENTITY_TYPE,
                'parentId' => $ids,
            ])
            ->group('parentId')
            ->group('type')
            ->build();

        /** @var array<int, array{count: int, type: string, parentId: string}> $rows */
        $rows = $this->entityManager
            ->getQueryExecutor()
            ->execute($query)
            ->fetchAll();

        foreach ($notes as $note) {
            $counts = [];

            foreach ($rows as $row) {
                if ($row['parentId'] !== $note->getId()) {
                    continue;
                }

                $counts[$row['type']] = $row['count'];
            }

            $note->set('reactionCounts', $counts);
        }
    }

    private function noAvailableReactions(): bool
    {
        return $this->config->get('availableReactions', []) === [];
    }
}
