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

namespace Espo\Classes\RecordHooks\Note;

use Espo\Core\Acl;
use Espo\Core\Acl\Permission;
use Espo\Core\Acl\Table as AclTable;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Name\Field;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Repositories\User as UserRepository;

/**
 * @implements SaveHook<Note>
 */
class AssignmentCheck implements SaveHook
{
    public function __construct(
        private User $user,
        private Acl $acl,
        private EntityManager $entityManager
    ) {}

    public function process(Entity $entity): void
    {
        $targetType = $entity->getTargetType();

        if (!$targetType) {
            return;
        }

        $userTeamIdList = $this->user->getTeamIdList();

        $userIdList = $entity->getLinkMultipleIdList('users');
        $portalIdList = $entity->getLinkMultipleIdList('portals');
        $teamIdList = $entity->getLinkMultipleIdList(Field::TEAMS);

        /** @var iterable<User> $targetUserList */
        $targetUserList = [];

        if ($targetType === Note::TARGET_USERS) {
            /** @var iterable<User> $targetUserList */
            $targetUserList = $this->entityManager
                ->getRDBRepository(User::ENTITY_TYPE)
                ->select([Attribute::ID, 'type'])
                ->where([Attribute::ID => $userIdList])
                ->find();
        }

        $hasPortalTargetUser = false;
        $allTargetUsersArePortal = true;

        foreach ($targetUserList as $user) {
            if (!$user->isPortal()) {
                $allTargetUsersArePortal = false;
            }

            if ($user->isPortal()) {
                $hasPortalTargetUser = true;
            }
        }

        $messagePermission = $this->acl->getPermissionLevel(Permission::MESSAGE);

        if ($messagePermission === AclTable::LEVEL_NO) {
            if (
                $targetType !== Note::TARGET_SELF &&
                $targetType !== Note::TARGET_PORTALS &&
                !(
                    $targetType === Note::TARGET_USERS &&
                    count($userIdList) === 1 &&
                    $userIdList[0] === $this->user->getId()
                ) &&
                !(
                    $targetType === Note::TARGET_USERS && $allTargetUsersArePortal
                )
            ) {
                throw new Forbidden('Not permitted to post to anybody except self.');
            }
        }

        if ($targetType === Note::TARGET_TEAMS) {
            if (empty($teamIdList)) {
                throw new BadRequest("No team IDS.");
            }
        }

        if ($targetType === Note::TARGET_USERS) {
            if (empty($userIdList)) {
                throw new BadRequest("No user IDs.");
            }
        }

        if ($targetType === Note::TARGET_PORTALS) {
            if (empty($portalIdList)) {
                throw new BadRequest("No portal IDs.");
            }

            if ($this->acl->getPermissionLevel(Permission::PORTAL) !== AclTable::LEVEL_YES) {
                throw new Forbidden('Not permitted to post to portal users.');
            }
        }

        if (
            $targetType === Note::TARGET_USERS &&
            $this->acl->getPermissionLevel(Permission::PORTAL) !== AclTable::LEVEL_YES
        ) {
            if ($hasPortalTargetUser) {
                throw new Forbidden('Not permitted to post to portal users.');
            }
        }

        if ($messagePermission === AclTable::LEVEL_TEAM) {
            if ($targetType === Note::TARGET_ALL) {
                throw new Forbidden('Not permitted to post to all.');
            }
        }

        if (
            $messagePermission === AclTable::LEVEL_TEAM &&
            $targetType === Note::TARGET_TEAMS
        ) {
            if (empty($userTeamIdList)) {
                throw new Forbidden('Not permitted to post to foreign teams.');
            }

            foreach ($teamIdList as $teamId) {
                if (!in_array($teamId, $userTeamIdList)) {
                    throw new Forbidden("Not permitted to post to foreign teams.");
                }
            }
        }

        if (
            $messagePermission === AclTable::LEVEL_TEAM &&
            $targetType === Note::TARGET_USERS
        ) {
            if (empty($userTeamIdList)) {
                throw new Forbidden('Not permitted to post to users from foreign teams.');
            }

            foreach ($targetUserList as $user) {
                if ($user->getId() === $this->user->getId()) {
                    continue;
                }

                if ($user->isPortal()) {
                    continue;
                }

                $inTeam = $this->getUserRepository()->checkBelongsToAnyOfTeams($user->getId(), $userTeamIdList);

                if (!$inTeam) {
                    throw new Forbidden('Not permitted to post to users from foreign teams.');
                }
            }
        }
    }

    private function getUserRepository(): UserRepository
    {
        /** @var UserRepository */
        return $this->entityManager->getRepository(User::ENTITY_TYPE);
    }
}
