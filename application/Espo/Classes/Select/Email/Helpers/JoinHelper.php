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

namespace Espo\Classes\Select\Email\Helpers;

use Espo\Entities\Email;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class JoinHelper
{
    public function joinEmailUser(QueryBuilder $queryBuilder, string $userId): void
    {
        if ($queryBuilder->hasJoinAlias(Email::ALIAS_INBOX)) {
            return;
        }

        $queryBuilder->leftJoin(Email::RELATIONSHIP_EMAIL_USER, Email::ALIAS_INBOX, [
            Email::ALIAS_INBOX . '.emailId:' => 'id',
            Email::ALIAS_INBOX . '.deleted' => false,
            Email::ALIAS_INBOX . '.userId' => $userId,
        ]);
    }
}
