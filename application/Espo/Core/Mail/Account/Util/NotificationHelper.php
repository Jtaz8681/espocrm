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

namespace Espo\Core\Mail\Account\Util;

use Espo\Core\Field\DateTime;
use Espo\Core\Field\LinkParent;
use Espo\Core\Mail\Account\Account;
use Espo\Core\Utils\Language;
use Espo\Entities\InboundEmail;
use Espo\Entities\Notification;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

class NotificationHelper
{
    private const PERIOD = '1 day';
    private const WAIT_PERIOD = '10 minutes';

    public function __construct(
        private EntityManager $entityManager,
        private Language $language
    ) {}

    public function processImapError(Account $account): void
    {
        $userId = $account->getUser()?->getId();
        $id = $account->getId();
        $entityType = $account->getEntityType();

        if (!$id) {
            return;
        }

        if (
            $account->getConnectedAt() &&
            DateTime::createNow()
                ->modify('-' . self::WAIT_PERIOD)
                ->isLessThan($account->getConnectedAt())
        ) {
            return;
        }

        $userIds = [];

        if ($entityType === InboundEmail::ENTITY_TYPE) {
            $userIds = $this->getAdminUserIds();
        } else if ($userId) {
            $userIds[] = $userId;
        }

        foreach ($userIds as $userId) {
            $this->processImapErrorForUser($entityType, $id, $userId);
        }
    }

    private function exists(string $entityType, string $id, string $userId): bool
    {
        $one = $this->entityManager
            ->getRDBRepositoryByClass(Notification::class)
            ->where([
                'relatedId' => $id,
                'relatedType' => $entityType,
                'userId' => $userId,
                'createdAt>' => DateTime::createNow()->modify('-' . self::PERIOD)->toString(),
            ])
            ->findOne();

        return $one !== null;
    }

    private function getMessage(string $entityType, string $id): string
    {
        $message = $this->language->translateLabel('imapNotConnected', 'messages', $entityType);

        return str_replace('{id}', $id, $message);
    }

    private function processImapErrorForUser(string $entityType, string $id, string $userId): void
    {
        if ($this->exists($entityType, $id, $userId)) {
            return;
        }

        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $message = $this->getMessage($entityType, $id);

        $notification
            ->setType(Notification::TYPE_MESSAGE)
            ->setMessage($message)
            ->setUserId($userId)
            ->setRelated(LinkParent::create($entityType, $id));

        $this->entityManager->saveEntity($notification);
    }

    /**
     * @return string[]
     */
    private function getAdminUserIds(): array
    {
        $users = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->select([Attribute::ID])
            ->where([
                'isActive' => true,
                'type' => User::TYPE_ADMIN,
            ])
            ->find();

        $ids = [];

        foreach ($users as $user) {
            $ids[] = $user->getId();
        }

        return $ids;
    }
}
