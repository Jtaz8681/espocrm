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

namespace Espo\Core\Formula;

use Espo\Core\Formula\Exceptions\Error;

/**
 * A function.
 *
 * An entity passed to the constructor as of v10.0.0. But it is not an officially guaranteed contract.
 */
interface Func
{
    /**
     * Process.
     *
     * @param EvaluatedArgumentList $arguments
     * @throws Error
     */
    public function process(EvaluatedArgumentList $arguments): mixed;
}
