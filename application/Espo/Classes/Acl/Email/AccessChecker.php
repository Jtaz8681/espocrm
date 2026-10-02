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

namespace Espo\Classes\Acl\Email;

use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\Entities\Email;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityCREDSChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;

/**
 * @implements AccessEntityCREDSChecker<Email>
 */
class AccessChecker implements AccessEntityCREDSChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(DefaultAccessChecker $defaultAccessChecker)
    {
        $this->defaultAccessChecker = $defaultAccessChecker;
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        /** @var Email $entity */

        if ($this->defaultAccessChecker->checkEntityRead($user, $entity, $data)) {
            return true;
        }

        if ($data->isFalse()) {
            return false;
        }

        if ($data->getRead() === Table::LEVEL_NO) {
            return false;
        }

        if (!$entity->has('usersIds')) {
            $entity->loadLinkMultipleField('users');
        }

        $userIdList = $entity->get('usersIds');

        if (is_array($userIdList) && in_array($user->getId(), $userIdList)) {
            return true;
        }

        return false;
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        /** @var Email $entity */

        if ($user->isAdmin()) {
            return true;
        }

        if ($data->isFalse()) {
            return false;
        }

        if ($data->getDelete() === Table::LEVEL_OWN) {
            if ($user->getId() === $entity->get('assignedUserId')) {
                return true;
            }

            if ($user->getId() === $entity->get('createdById')) {
                return true;
            }

            $assignedUserIdList = $entity->getLinkMultipleIdList(Field::ASSIGNED_USERS);

            if (
                count($assignedUserIdList) === 1 &&
                $entity->hasLinkMultipleId(Field::ASSIGNED_USERS, $user->getId())
            ) {
                return true;
            }

            return false;
        }

        if ($this->defaultAccessChecker->checkEntityDelete($user, $entity, $data)) {
            return true;
        }

        if ($data->getEdit() === Table::LEVEL_NO && $data->getCreate() === Table::LEVEL_NO) {
            return false;
        }

        if ($entity->get('createdById') !== $user->getId()) {
            return false;
        }

        if (
            $entity->getStatus() !== Email::STATUS_SENT &&
            $entity->getStatus() !== Email::STATUS_ARCHIVED
        ) {
            return true;
        }

        return false;
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        /** @var Email $entity */

        if (
            $entity->getStatus() === Email::STATUS_DRAFT &&
            $entity->getCreatedBy() &&
            $entity->getCreatedBy()->getId() === $user->getId()
        ) {
            return true;
        }

        return $this->defaultAccessChecker->checkEntityEdit($user, $entity, $data);
    }

    public function checkEdit(User $user, ScopeData $data): bool
    {
        if ($data->getCreate() === Table::LEVEL_YES) {
            return true;
        }

        return $this->defaultAccessChecker->checkEdit($user, $data);
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        if ($data->getCreate() === Table::LEVEL_YES) {
            return true;
        }

        return $this->defaultAccessChecker->checkDelete($user, $data);
    }
}
