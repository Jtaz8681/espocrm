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
use Espo\Classes\Select\Email\Helpers\EmailAddressHelper;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem as WhereClauseItem;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class ToEquals implements ItemConverter
{
    protected string $addressType = 'to';

    public function __construct(
        private EmailAddressHelper $emailAddressHelper,
    ) {}

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $value = $item->getValue();

        if (!$value) {
            return WhereClause::fromRaw([Attribute::ID => null]);
        }

        $emailAddressId = $this->emailAddressHelper->getEmailAddressIdByValue($value);

        if (!$emailAddressId) {
            return WhereClause::fromRaw([Attribute::ID => null]);
        }

        return Cond::exists(
            SelectBuilder::create()
                ->from('EmailEmailAddress', 'sq')
                ->where([
                    'emailAddressId' => $emailAddressId,
                    'addressType' => $this->addressType,
                ])
                ->where(
                    Cond::equal(
                        Expr::column('sq.emailId'),
                        Expr::column('email.id')
                    )
                )
                ->build()
        );
    }
}
