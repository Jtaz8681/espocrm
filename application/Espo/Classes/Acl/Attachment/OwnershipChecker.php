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

namespace Espo\Classes\Acl\Attachment;

use Espo\Entities\Attachment;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\OwnershipOwnChecker;

/**
 * @implements OwnershipOwnChecker<Attachment>
 */
class OwnershipChecker implements OwnershipOwnChecker
{
    private const ATTR_CREATED_BY_ID = 'createdById';

    public function checkOwn(User $user, Entity $entity): bool
    {
        if ($user->getId() === $entity->get(self::ATTR_CREATED_BY_ID)) {
            return true;
        }

        return false;
    }
}
