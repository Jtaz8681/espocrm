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

namespace Espo\Core\Acl\Table;

use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;

/**
 * @internal
 */
class ScopeDataResolver
{
    public function __construct(
        private Table $table,
    ) {}

    public function resolve(mixed $data): ScopeData
    {
        if (!is_string($data)) {
            return ScopeData::fromRaw($data);
        }

        $foreignScope = $data;
        $isBoolean = false;

        if (str_starts_with($data, 'boolean:')) {
            [, $foreignScope] = explode(':', $data, 2);
            $isBoolean = true;
        }

        $scopeData = $this->table->getScopeData($foreignScope);

        if ($isBoolean && !$scopeData->isBoolean()) {
            return ScopeData::fromRaw(true);
        }

        return $scopeData;
    }
}
