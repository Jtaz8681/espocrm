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

namespace Espo\Tools\Notification;

use Espo\Core\AclManager as InternalAclManager;
use Espo\Core\Acl\Table;

use Espo\Core\Name\Field;
use Espo\ORM\Name\Attribute;
use Espo\Tools\Notification\HookProcessor\Params;
use Espo\Tools\Stream\Service as StreamService;

use Espo\ORM\EntityManager;
use Espo\ORM\SthCollection;
use Espo\ORM\EntityCollection;

use Espo\Entities\User;
use Espo\Entities\Team;
use Espo\Entities\Portal;
use Espo\Entities\Notification;
use Espo\Entities\Note;

/**
 * Handles notifications after note saving.
 */
class NoteHookProcessor
{
    public function __construct(
        private StreamService $streamService,
        private Service $service,
        private EntityManager $entityManager,
        private User $user,
        private InternalAclManager $internalAclManager
    ) {}

    public function afterSave(Note $note, Params $params): void
    {
        if ($note->getParentType() && $note->getParentId()) {
            $this->afterSaveParent($note, $params);

            return;
        }

        $this->afterSaveNoParent($note);
    }

    private function afterSaveParent(Note $note, Params $params): void
    {
        $parentType = $note->getParentType();
        $parentId = $note->getParentId();
        $superParentType = $note->getSuperParentType();
        $superParentId = $note->getSuperParentId();

        if (!$parentType || !$parentId) {
            return;
        }

        $users = $this->getSubscriberList($parentType, $parentId, $note->isInternal());

        $regularUserIds = [];

        foreach ($users as $user) {
            $regularUserIds[] = $user->getId();
        }

        $superUserIds = [];

        if ($superParentType && $superParentId) {
            $superUsers = $this->getSubscriberList($superParentType, $superParentId, $note->isInternal());

            $superUsers = $superUsers->filter(function (User $user) use ($regularUserIds) {
                return !$user->isPortal() && !in_array($user->getId(), $regularUserIds);
            });

            foreach ($superUsers as $user) {
                $superUserIds[] = $user->getId();

                $users[] = $user;
            }
        }

        $targetType = $note->getRelatedType() ? $note->getRelatedType() : $parentType;

        // This is correct.
        $skipAclCheck = !$note->isAclProcessed();

        $teamIdList = null;
        $regularUserIds = null;

        if (!$skipAclCheck) {
            $teamIdList = $note->getLinkMultipleIdList(Field::TEAMS);
            $regularUserIds = $note->getLinkMultipleIdList('users');
        }

        $notifyUserIdList = [];

        foreach ($users as $user) {
            if ($skipAclCheck) {
                $notifyUserIdList[] = $user->getId();

                continue;
            }

            /** @var string[] $regularUserIds */
            /** @var string[] $teamIdList */

            if ($user->isAdmin()) {
                $notifyUserIdList[] = $user->getId();

                continue;
            }

            if ($user->isPortal() && $note->getRelatedType()) {
                continue;
            }

            if ($user->isPortal()) {
                $notifyUserIdList[] = $user->getId();

                continue;
            }

            $level = $this->internalAclManager->getLevel($user, $targetType, Table::ACTION_READ);

            if (!$this->checkUserAccess($user, $level, $teamIdList, $regularUserIds)) {
                continue;
            }

            $notifyUserIdList[] = $user->getId();
        }

        $regularIds = array_unique(array_diff($notifyUserIdList, $superUserIds));
        $superIds = array_unique(array_intersect($notifyUserIdList, $superUserIds));

        $this->processNotify($note, $regularIds, $params);
        $this->processNotify($note, $superIds, $params, true);
    }

    private function afterSaveNoParent(Note $note): void
    {
        $targetType = $note->getTargetType();

        if ($targetType === Note::TARGET_USERS) {
            $this->afterSaveTargetUsers($note);

            return;
        }

        if ($targetType === Note::TARGET_TEAMS) {
            $this->afterSaveTargetTeams($note);

            return;
        }

        if ($targetType === Note::TARGET_PORTALS) {
            $this->afterSaveTargetPortals($note);

            return;
        }

        if ($targetType === Note::TARGET_ALL) {
            $this->afterSaveTargetAll($note);
        }
    }

    /**
     * @param string[] $userIdList
     */
    private function processNotify(Note $note, array $userIdList, ?Params $params = null, bool $isSuper = false): void
    {
        $filteredUserIdList = array_filter(
            $userIdList,
            function (string $userId) use ($note) {
                if ($note->isUserIdNotified($userId)) {
                    return false;
                }

                if ($note->isNew()) {
                    return true;
                }

                $existing = $this->entityManager
                    ->getRDBRepository(Notification::ENTITY_TYPE)
                    ->select([Attribute::ID])
                    ->where([
                        'type' => Notification::TYPE_NOTE,
                        'relatedType' => Note::ENTITY_TYPE,
                        'relatedId' => $note->getId(),
                        'userId' => $userId,
                    ])
                    ->findOne();

                if ($existing) {
                    return false;
                }

                return true;
            }
        );

        if (!count($filteredUserIdList)) {
            return;
        }

        $notifyParams = new NoteNotifyParams(isSuper: $isSuper);

        $this->service->notifyAboutNote(
            userIdList: $filteredUserIdList,
            note: $note,
            params: $params,
            notifyParams: $notifyParams,
        );
    }

    private function afterSaveTargetUsers(Note $note): void
    {
        $targetUserIdList = $note->get('usersIds') ?? [];

        if (!count($targetUserIdList)) {
            return;
        }

        $notifyUserIdList = [];

        foreach ($targetUserIdList as $userId) {
            if ($userId === $this->user->getId()) {
                continue;
            }

            $notifyUserIdList[] = $userId;
        }

        $this->processNotify($note, array_unique($notifyUserIdList));
    }

    private function afterSaveTargetTeams(Note $note): void
    {
        $targetTeamIdList = $note->get('teamsIds') ?? [];

        if (!count($targetTeamIdList)) {
            return;
        }

        $notifyUserIdList = [];

        foreach ($targetTeamIdList as $teamId) {
            $team = $this->entityManager->getEntityById(Team::ENTITY_TYPE, $teamId);

            if (!$team) {
                continue;
            }

            $targetUserList = $this->entityManager
                ->getRDBRepository(Team::ENTITY_TYPE)
                ->getRelation($team, 'users')
                ->where([
                    'isActive' => true,
                ])
                ->select(Attribute::ID)
                ->find();

            foreach ($targetUserList as $user) {
                if ($user->getId() === $this->user->getId()) {
                    continue;
                }

                $notifyUserIdList[] = $user->getId();
            }
        }

        $this->processNotify($note, array_unique($notifyUserIdList));
    }

    private function afterSaveTargetPortals(Note $note): void
    {
        $targetPortalIdList = $note->get('portalsIds') ?? [];

        if (!count($targetPortalIdList)) {
            return;
        }

        $notifyUserIdList = [];

        foreach ($targetPortalIdList as $portalId) {
            $portal = $this->entityManager->getEntityById(Portal::ENTITY_TYPE, $portalId);

            if (!$portal) {
                continue;
            }

            $targetUserList = $this->entityManager
                ->getRDBRepository(Portal::ENTITY_TYPE)
                ->getRelation($portal, 'users')
                ->where([
                    'isActive' => true,
                ])
                ->select([Attribute::ID])
                ->find();

            foreach ($targetUserList as $user) {
                if ($user->getId() === $this->user->getId()) {
                    continue;
                }

                $notifyUserIdList[] = $user->getId();
            }
        }

        $this->processNotify($note, array_unique($notifyUserIdList));
    }

    private function afterSaveTargetAll(Note $note): void
    {
        $targetUserList = $this->entityManager
            ->getRDBRepository(User::ENTITY_TYPE)
            ->where([
                'isActive' => true,
                'type' => ['regular', 'admin'],
            ])
            ->select(Attribute::ID)
            ->find();

        $notifyUserIdList = [];

        foreach ($targetUserList as $user) {
            if ($user->getId() === $this->user->getId()) {
                continue;
            }

            $notifyUserIdList[] = $user->getId();
        }

        $this->processNotify($note, $notifyUserIdList);
    }

    /**
     * @param string[] $teamIdList
     * @param string[] $userIdList
     * @return bool
     */
    private function checkUserAccess(
        User $user,
        string $level,
        array $teamIdList,
        array $userIdList
    ): bool {

        if ($level === Table::LEVEL_ALL) {
            return true;
        }

        if ($level === Table::LEVEL_TEAM) {
            if (in_array($user->getId(), $userIdList)) {
                return true;
            }

            if (!count($teamIdList)) {
                return false;
            }

            $userTeamIdList = $user->getLinkMultipleIdList(Field::TEAMS);

            foreach ($teamIdList as $teamId) {
                if (in_array($teamId, $userTeamIdList)) {
                    return true;
                }
            }

            return false;
        }

        if ($level === Table::LEVEL_OWN) {
            return in_array($user->getId(), $userIdList);
        }

        return false;
    }

    /**
     * @return EntityCollection<User>
     */
    private function getSubscriberList(string $parentType, string $parentId, bool $isInternal = false): EntityCollection
    {
        $collection = $this->streamService->getSubscriberList($parentType, $parentId, $isInternal);

        if ($collection instanceof EntityCollection) {
            return $collection;
        }

        if ($collection instanceof SthCollection) {
            /** @var EntityCollection<User> */
            return $this->entityManager
                ->getCollectionFactory()
                ->createFromSthCollection($collection);
        }

        /** @var EntityCollection<User> $newCollection */
        $newCollection = $this->entityManager
            ->getCollectionFactory()
            ->create(User::ENTITY_TYPE);

        foreach ($collection as $entity) {
            $newCollection[] = $entity;
        }

        return $newCollection;
    }
}
