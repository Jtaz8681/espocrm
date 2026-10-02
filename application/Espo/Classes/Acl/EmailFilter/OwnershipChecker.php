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

namespace Espo\Classes\Acl\EmailFilter;

use Espo\Entities\EmailAccount;
use Espo\Entities\User;
use Espo\Entities\EmailFilter;
use Espo\ORM\Entity;
use Espo\Core\Acl\OwnershipOwnChecker;
use Espo\Core\ORM\EntityManager;

/**
 * @implements OwnershipOwnChecker<EmailFilter>
 */
class OwnershipChecker implements OwnershipOwnChecker
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param EmailFilter $entity
     */
    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($entity->isGlobal()) {
            return false;
        }

        $parentType = $entity->getParentType();
        $parentId = $entity->getParentId();

        if (!$parentType || !$parentId) {
            return false;
        }

        $parent = $this->entityManager->getEntityById($parentType, $parentId);

        if (!$parent) {
            return false;
        }

        if ($parent->getEntityType() === User::ENTITY_TYPE) {
            return $parent->getId() === $user->getId();
        }

        if (
            $parent instanceof EmailAccount &&
            $parent->has('assignedUserId') &&
            $parent->get('assignedUserId') === $user->getId()
        ) {
            return true;
        }

        return false;
    }
}
