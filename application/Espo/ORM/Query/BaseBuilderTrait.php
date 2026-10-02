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

trait BaseBuilderTrait
{
    /**
     * Must be protected for compatibility reasons.
     *
     * @var array<string, mixed>
     */
    protected $params = [];

    public function __construct()
    {
    }

    private function isEmpty(): bool
    {
        return empty($this->params);
    }

    private function cloneInternal(Query $query): void
    {
        if (!$this->isEmpty()) {
            throw new RuntimeException("Clone can be called only on a new empty builder instance.");
        }

        $this->params = $query->getRaw();
    }
}
