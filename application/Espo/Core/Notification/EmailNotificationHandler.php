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

namespace Espo\Core\Notification;

use Espo\Core\Mail\SenderParams;
use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\Entities\Email;

/**
 * Handles a notification emails (supposed for adding CC, BCC addresses).
 * Provides sender parameters for notification emails (e.g. setting Reply-To address).
 */
interface EmailNotificationHandler
{
    public function prepareEmail(Email $email, Entity $entity, User $user): void;

    public function getSenderParams(Entity $entity, User $user): ?SenderParams;
}
