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

namespace Espo\Classes\Select\Webhook\AccessControlFilters;

use Espo\ORM\Query\SelectBuilder;

use Espo\Core\Select\AccessControl\Filter;

use Espo\Entities\User;

class Mandatory implements Filter
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(SelectBuilder $queryBuilder): void
    {
        if ($this->user->isAdmin()) {
            return;
        }

        if (!$this->user->isApi()) {
            $queryBuilder->where([
                'id' => null,
            ]);

            return;
        }

        $queryBuilder->where([
            'userId' => $this->user->getId()
        ]);
    }
}
