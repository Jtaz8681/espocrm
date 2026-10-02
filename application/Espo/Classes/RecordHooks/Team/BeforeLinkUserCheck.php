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

namespace Espo\Classes\RecordHooks\Team;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\LinkHook;

use Espo\ORM\Entity;

use Espo\Entities\User;

/**
 * @implements LinkHook<\Espo\Entities\Team>
 */
class BeforeLinkUserCheck implements LinkHook
{
    public function process(Entity $entity, string $link, Entity $foreignEntity): void
    {
        if ($link !== 'users') {
            return;
        }

        assert($foreignEntity instanceof User);

        $this->processUserCheck($foreignEntity);
    }

    private function processUserCheck(User $user): void
    {
        if ($user->isPortal()) {
            throw new Forbidden("Can't add portal users to team.");
        }

        if ($user->isSystem()) {
            throw new Forbidden("Can't add system users to team.");
        }
    }
}
