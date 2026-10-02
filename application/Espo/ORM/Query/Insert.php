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

namespace Espo\ORM\Query;

use RuntimeException;

/**
 * Insert parameters.
 *
 * Immutable.
 */
class Insert implements Query
{
    use BaseTrait;

    /**
     * @param array<string, mixed> $params
     */
    private function validateRawParams(array $params): void
    {
        $into = $params['into'] ?? null;

        if (!$into || !is_string($into)) {
            throw new RuntimeException("Bad or missing 'into' parameter.");
        }

        $columns = $params['columns'] ?? [];

        if (!is_array($columns)) {
            throw new RuntimeException("Bad 'columns' parameter.");
        }

        $values = $params['values'] ?? [];

        if (!is_array($values)) {
            throw new RuntimeException("Bad 'values' parameter.");
        }

        $updateSet = $params['updateSet'] ?? null;

        if ($updateSet && !is_array($updateSet)) {
            throw new RuntimeException("Bad 'updateSet' parameter.");
        }
    }
}
