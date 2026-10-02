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

namespace Espo\Tools\UserReaction;

use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Entities\Note;
use Espo\Entities\Notification;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\Tools\Stream\Service;

class NotificationService
{
    /** @var array<string, ?Preferences>  */
    private array $preferencesMap = [];

    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Service $streamService,
    ) {}

    public function notifyNote(Note $note, string $type): void
    {
        $recipientId = $note->getCreatedById();

        if (!$recipientId || $recipientId === $this->user->getId()) {
            return;
        }

        $parent = $note->getParent();

        if (
            $parent &&
            !$this->isEnabledForUserForNotFollowed($recipientId) &&
            !$this->streamService->checkIsFollowed($parent, $note->getCreatedById())
        ) {
            return;
        }

        if (!$this->isEnabledForUser($recipientId)) {
            return;
        }

        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $data = [
            'type' => $type,
            'userId' => $this->user->getId(),
            'userName' => $this->user->getName(),
        ];

        $notification
            ->setType(Notification::TYPE_USER_REACTION)
            ->setUserId($recipientId)
            ->setRelated(LinkParent::fromEntity($note));

        if ($parent instanceof Entity) {
            $notification->setRelatedParent($parent);
            $data['entityName'] = $parent->get(Field::NAME);
        }

        $notification->setData($data);

        $this->entityManager->saveEntity($notification);
    }

    private function isEnabledForUser(string $recipientId): bool
    {
        $recipientPreferences = $this->getPreferences($recipientId);

        return $recipientPreferences && $recipientPreferences->get('reactionNotifications');
    }

    private function isEnabledForUserForNotFollowed(string $recipientId): bool
    {
        $recipientPreferences = $this->getPreferences($recipientId);

        return $recipientPreferences && $recipientPreferences->get('reactionNotificationsNotFollowed');
    }

    public function removeNoteUnread(Note $note, User $user, ?string $type = null): void
    {
        $notifications = $this->entityManager
            ->getRDBRepositoryByClass(Notification::class)
            ->where([
                'read' => false,
                'createdById' => $user->getId(),
                'type' => Notification::TYPE_USER_REACTION,
                'relatedId' => $note->getId(),
                'relatedType' => $note->getEntityType(),
            ])
            ->find();

        foreach ($notifications as $notification) {
            if ($type && $notification->getData()?->type !== $type) {
                continue;
            }

            $this->entityManager->removeEntity($notification);
        }
    }

    private function getPreferences(string $id): ?Preferences
    {
        if (!array_key_exists($id, $this->preferencesMap)) {
            $this->preferencesMap[$id] = $this->entityManager->getRepositoryByClass(Preferences::class)->getById($id);
        }

        return $this->preferencesMap[$id];
    }
}
