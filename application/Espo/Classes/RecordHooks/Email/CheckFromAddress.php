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

namespace Espo\Classes\RecordHooks\Email;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Mail\Account\SendingAccountProvider;
use Espo\Core\Mail\ConfigDataProvider;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<Email>
 */
class CheckFromAddress implements SaveHook
{
    public function __construct(
        private User $user,
        private SendingAccountProvider $sendingAccountProvider,
        private Acl $acl,
        private ConfigDataProvider $configDataProvider,
    ) {}

    public function process(Entity $entity): void
    {
        if ($this->user->isAdmin()) {
            return;
        }

        $fromAddress = $entity->getFromAddress();

        // Should be after 'getFromAddress'.
        if (!$entity->isAttributeChanged('from')) {
            return;
        }

        if (!$fromAddress) {
            throw new BadRequest("No 'from' address");
        }

        if ($this->acl->checkScope('Import')) {
            return;
        }

        $fromAddress = strtolower($fromAddress);

        foreach ($this->user->getEmailAddressGroup()->getAddressList() as $address) {
            if ($fromAddress === strtolower($address)) {
                return;
            }
        }

        if ($this->sendingAccountProvider->getShared($this->user, $fromAddress)) {
            return;
        }

        $system = $this->sendingAccountProvider->getSystem();

        if (
            $system &&
            $this->configDataProvider->isSystemOutboundAddressShared() &&
            $system->getEmailAddress() &&
            $fromAddress === strtolower($system->getEmailAddress())
        ) {
            return;
        }

        throw new Forbidden("Not allowed 'from' address.");
    }
}
