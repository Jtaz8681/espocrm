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

namespace Espo\Classes\RecordHooks\EmailFolder;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\EmailFolder;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<EmailFolder>
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private User $user,
        private Acl $acl
    ) {}

    public function process(Entity $entity): void
    {
        if (!$this->user->isAdmin() || !$entity->get('assignedUserId')) {
            $entity->set('assignedUserId', $this->user->getId());
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }
    }
}
