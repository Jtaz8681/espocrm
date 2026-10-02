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

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config;
use Espo\Core\WebSocket\Submission as WebSocketSubmission;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\Entities\UserReaction;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\DeleteBuilder;
use Espo\Tools\UserReaction\NotificationService;

class MyReactionsService
{
    public function __construct(
        private Config $config,
        private EntityManager $entityManager,
        private User $user,
        private WebSocketSubmission $webSocketSubmission,
        private NotificationService $notificationService,
    ) {}

    /**
     * @throws Forbidden
     */
    public function react(Note $note, string $type): void
    {
        if (!$this->isReactionAllowed($type)) {
            throw new Forbidden("Not allowed reaction '$type'.");
        }

        if ($note->getType() !== Note::TYPE_POST) {
            throw new Forbidden("Cannot react on non-post note.");
        }

        $this->entityManager->getTransactionManager()->run(function () use ($type, $note) {
            $repository = $this->entityManager->getRDBRepositoryByClass(UserReaction::class);

            $found = $repository
                ->forUpdate()
                ->where([
                    'userId' => $this->user->getId(),
                    'parentType' => Note::ENTITY_TYPE,
                    'parentId' => $note->getId(),
                    'type' => $type,
                ])
                ->findOne();

            if ($found) {
                return;
            }

            $this->deleteAll($note);
            $this->notificationService->removeNoteUnread($note, $this->user);

            $reaction = $repository->getNew();

            $reaction
                ->setParent($note)
                ->setUser($this->user)
                ->setType($type);

            $this->entityManager->saveEntity($reaction);
        });

        $this->webSocketSubmit($note);
        $this->notificationService->notifyNote($note, $type);
    }

    public function unReact(Note $note, string $type): void
    {
        $repository = $this->entityManager->getRDBRepositoryByClass(UserReaction::class);

        $reaction = $repository
            ->where([
                'userId' => $this->user->getId(),
                'parentType' => $note->getEntityType(),
                'parentId' => $note->getId(),
                'type' => $type,
            ])
            ->findOne();

        if (!$reaction) {
            return;
        }

        $this->notificationService->removeNoteUnread($note, $this->user, $type);

        $this->entityManager->removeEntity($reaction);

        $this->webSocketSubmit($note);
    }

    private function isReactionAllowed(string $type): bool
    {
        /** @var string[] $allowedReactions */
        $allowedReactions = $this->config->get('availableReactions') ?? [];

        return in_array($type, $allowedReactions);
    }

    private function deleteAll(Note $note): void
    {
        $deleteQuery = DeleteBuilder::create()
            ->from(UserReaction::ENTITY_TYPE)
            ->where([
                'userId' => $this->user->getId(),
                'parentType' => Note::ENTITY_TYPE,
                'parentId' => $note->getId(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($deleteQuery);
    }

    private function webSocketSubmit(Note $note): void
    {
        $topic = "streamUpdate.{$note->getParentType()}.{$note->getParentId()}";

        $this->webSocketSubmission->submit($topic, null, ['noteId' => $note->getId()]);

        $topicUpdate = "recordUpdate.Note.{$note->getId()}";

        $this->webSocketSubmission->submit($topicUpdate);
    }
}
