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

namespace Espo\Core\Select\Text;

/**
 * Immutable.
 */
class FilterParams
{
    private bool $noFullTextSearch = false;

    private function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public function withNoFullTextSearch(bool $noFullTextSearch = true): self
    {
        $obj = clone $this;
        $obj->noFullTextSearch = $noFullTextSearch;

        return $obj;
    }

    public function noFullTextSearch(): bool
    {
        return $this->noFullTextSearch;
    }
}
