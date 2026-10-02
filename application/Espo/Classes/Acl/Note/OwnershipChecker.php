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

namespace Espo\Classes\Acl\Note;

use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\OwnershipOwnChecker;

/**
 * @implements OwnershipOwnChecker<Note>
 */
class OwnershipChecker implements OwnershipOwnChecker
{
    /**
     * @param Note $entity
     */
    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($entity->getType() === Note::TYPE_POST && $user->getId() === $entity->getCreatedById()) {
            return true;
        }

        return false;
    }
}
