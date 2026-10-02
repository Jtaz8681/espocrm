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

namespace Espo\Classes\Select\Email\Where\ItemConverters;

use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\ItemConverter;
use Espo\Classes\Select\Email\Helpers\JoinHelper;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem as WhereClauseItem;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class IsImportantIsTrue implements ItemConverter
{
    public function __construct(private User $user, private JoinHelper $joinHelper)
    {}

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $this->joinHelper->joinEmailUser($queryBuilder, $this->user->getId());

        return WhereClause::fromRaw([
            Email::ALIAS_INBOX . '.isImportant' => true,
        ]);
    }
}
