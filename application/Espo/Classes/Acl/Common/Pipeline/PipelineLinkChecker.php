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

namespace Espo\Classes\Acl\Common\Pipeline;

use Espo\Core\Acl\LinkChecker;
use Espo\Entities\Pipeline;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements LinkChecker<Entity, Pipeline>
 */
class PipelineLinkChecker implements LinkChecker
{
    public function check(User $user, Entity $entity, Entity $foreignEntity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($foreignEntity->isAvailableForAll()) {
            return true;
        }

        if (!$user->isPortal()) {
            return false;
        }

        return array_intersect($user->getTeamIdList(), $foreignEntity->getTeams()->getIdList()) !== [];
    }
}
