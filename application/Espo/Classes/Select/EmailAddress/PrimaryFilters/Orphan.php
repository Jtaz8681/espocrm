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

namespace Espo\Classes\Select\EmailAddress\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;

class Orphan implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder
            ->distinct()
            ->leftJoin(
                'EntityEmailAddress',
                'entityEmailAddress',
                [
                    'emailAddressId:' => Attribute::ID,
                    Attribute::DELETED => false,
                ]
            )
            ->leftJoin(
                'EmailEmailAddress',
                'emailEmailAddress',
                [
                    'emailAddressId:' => Attribute::ID,
                    Attribute::DELETED => false,
                ]
            )
            ->leftJoin(
                'Email',
                'email',
                [
                    'fromEmailAddressId:' => Attribute::ID,
                    Attribute::DELETED => false,
                ]
            )
            ->where([
                'entityEmailAddress.id' => null,
                'emailEmailAddress.id' => null,
                'email.id' => null,
            ]);
    }
}
