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

use Espo\ORM\Query\Part\Expression;
use RuntimeException;

/**
 * Update parameters.
 *
 * Immutable.
 */
class Update implements Query
{
    use SelectingTrait;
    use BaseTrait;

    /**
     * Get an entity type.
     */
    public function getIn(): string
    {
        $in = $this->params['from'];

        if ($in === null) {
            throw new RuntimeException("Missing 'in'.");
        }

        return $in;
    }

    /**
     * Get a LIMIT.
     */
    public function getLimit(): ?int
    {
        return $this->params['limit'] ?? null;
    }

    /**
     * Get SET values.
     *
     * @return array<string, scalar|Expression|null>
     */
    public function getSet(): array
    {
        $set = [];
        /** @var array<string, ?scalar> $raw */
        $raw = $this->params['set'];

        foreach ($raw as $key => $value) {
            if (str_ends_with($key, ':')) {
                $key = substr($key, 0, -1);
                $value = Expression::create((string) $value);
            }

            $set[$key] = $value;
        }

        return $set;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateRawParams(array $params): void
    {
        $this->validateRawParamsSelecting($params);

        $from = $params['from'] ?? null;

        if (!$from || !is_string($from)) {
            throw new RuntimeException("Update params: Missing 'in'.");
        }

        $set = $params['set'] ?? null;

        if (!$set || !is_array($set)) {
            throw new RuntimeException("Update params: Bad or missing 'set' parameter.");
        }
    }
}
