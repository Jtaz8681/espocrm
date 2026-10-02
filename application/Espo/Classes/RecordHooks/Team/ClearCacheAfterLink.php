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

use Espo\Core\Acl\Cache\Clearer;
use Espo\Core\DataManager;
use Espo\Core\Record\Hook\LinkHook;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements LinkHook<Team>
 */
class ClearCacheAfterLink implements LinkHook
{
    public function __construct(
        private Clearer $clearer,
        private DataManager $dataManager
    ) {}

    public function process(Entity $entity, string $link, Entity $foreignEntity): void
    {
        if ($link !== 'users' || !$foreignEntity instanceof User) {
            return;
        }

        $this->clearer->clearForUser($foreignEntity);
        $this->dataManager->updateCacheTimestamp();
    }
}
