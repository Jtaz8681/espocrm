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

namespace Espo\Core\Select\AccessControl\FilterResolvers;

use Espo\Core\Acl;
use Espo\Core\Select\AccessControl\FilterResolver;
use Espo\Entities\User;

class BooleanOwn implements FilterResolver
{
    private string $entityType;
    private Acl $acl;
    private User $user;

    public function __construct(string $entityType, Acl $acl, User $user)
    {
        $this->entityType = $entityType;
        $this->acl = $acl;
        $this->user = $user;
    }

    public function resolve(): ?string
    {
        if (!$this->acl->checkScope($this->entityType)) {
            return 'no';
        }

        if ($this->user->isAdmin()) {
            return 'all';
        }

        return 'onlyOwn';
    }
}
