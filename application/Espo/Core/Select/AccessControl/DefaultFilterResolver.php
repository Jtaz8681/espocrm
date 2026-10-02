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

namespace Espo\Core\Select\AccessControl;

use Espo\Core\Acl;

class DefaultFilterResolver implements FilterResolver
{
    public function __construct(private string $entityType, private Acl $acl)
    {}

    public function resolve(): ?string
    {
        if ($this->acl->checkReadNo($this->entityType)) {
            return 'no';
        }

        if ($this->acl->checkReadOnlyOwn($this->entityType)) {
            return 'onlyOwn';
        }

        if ($this->acl->checkReadOnlyTeam($this->entityType)) {
            return 'onlyTeam';
        }

        if ($this->acl->checkReadAll($this->entityType)) {
            return 'all';
        }

        return 'no';
    }
}
