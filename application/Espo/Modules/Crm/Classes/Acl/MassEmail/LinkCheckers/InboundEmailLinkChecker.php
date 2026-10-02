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

namespace Espo\Modules\Crm\Classes\Acl\MassEmail\LinkCheckers;

use Espo\Core\Acl\LinkChecker;
use Espo\Entities\InboundEmail;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;

/**
 * @implements LinkChecker<MassEmail, InboundEmail>
 * @noinspection PhpUnused
 */
class InboundEmailLinkChecker implements LinkChecker
{
    public function check(User $user, Entity $entity, Entity $foreignEntity): bool
    {
        return $foreignEntity->smtpIsForMassEmail();
    }
}
