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

namespace Espo\Core\Portal\Acl\AccessChecker;

use Closure;

/**
 * Scope checker data.
 */
class ScopeCheckerData
{
    public function __construct(
        private Closure $isOwnChecker,
        private Closure $inAccountChecker,
        private Closure $inContactChecker
    ) {}

    public function isOwn(): bool
    {
        return ($this->isOwnChecker)();
    }

    public function inAccount(): bool
    {
        return ($this->inAccountChecker)();
    }

    public function inContact(): bool
    {
        return ($this->inContactChecker)();
    }

    public static function createBuilder(): ScopeCheckerDataBuilder
    {
        return new ScopeCheckerDataBuilder();
    }
}
