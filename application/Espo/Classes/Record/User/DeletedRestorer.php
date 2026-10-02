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

namespace Espo\Classes\Record\User;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Record\Deleted\DefaultRestorer;
use Espo\Core\Record\Deleted\Restorer;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Tools\User\UserUtil;

/**
 * @implements Restorer<User>
 */
class DeletedRestorer implements Restorer
{
    public function __construct(
        private DefaultRestorer $defaultRestorer,
        private UserUtil $userUtil,
    ) {}


    /**
     * @inheritDoc
     */
    public function restore(Entity $entity): void
    {
        $this->processUserExistsChecking($entity);

        $this->defaultRestorer->restore($entity);
    }

    /**
     * @throws Conflict
     */
    private function processUserExistsChecking(User $user): void
    {
        if ($this->userUtil->checkExists($user)) {
            throw new Conflict('userNameExists');
        }
    }
}
