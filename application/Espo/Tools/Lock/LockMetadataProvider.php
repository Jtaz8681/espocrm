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

namespace Espo\Tools\Lock;

use Espo\Core\Utils\Metadata;

class LockMetadataProvider
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function isEnabled(string $entityType): bool
    {
        if (!$this->metadata->get("scopes.$entityType.lockable")) {
            return false;
        }

        if (!$this->metadata->get("entityDefs.$entityType.fields.isLocked")) {
            return false;
        }

        return true;
    }
}
