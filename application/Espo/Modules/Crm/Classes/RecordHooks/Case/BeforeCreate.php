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

namespace Espo\Modules\Crm\Classes\RecordHooks\Case;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<CaseObj>
 * @noinspection PhpUnused
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private User $user
    ) {}

    public function process(Entity $entity): void
    {
        if (!$this->user->isPortal()) {
            return;
        }

        $userContact = $this->user->getContact();

        if (!$userContact) {
            return;
        }

        if (!$entity->getAccount() && $userContact->getAccount()) {
            $entity->setAccount($userContact->getAccount());
        }

        if (!$entity->getContact()) {
            $entity->setContact($userContact);
        }
    }
}
