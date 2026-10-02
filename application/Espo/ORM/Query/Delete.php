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
 * Delete parameters.
 *
 * Immutable.
 */
class Delete implements Query
{
    use SelectingTrait;
    use BaseTrait;

    /**
     * Get an entity type.
     */
    public function getFrom(): string
    {
        return $this->params['from'];
    }

    /**
     * Get a from-alias
     */
    public function getFromAlias(): ?string
    {
        return $this->params['fromAlias'] ?? null;
    }

    /**
     * Get a LIMIT.
     */
    public function getLimit(): ?int
    {
        return $this->params['limit'] ?? null;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateRawParams(array $params): void
    {
        $this->validateRawParamsSelecting($params);

        $from = $params['from'] ?? null;

        if (!$from || !is_string($from)) {
            throw new RuntimeException("Select params: Missing 'from'.");
        }
    }
}
