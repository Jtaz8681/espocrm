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

namespace Espo\Core\Upgrades\Actions\Extension;

use Espo\Core\Exceptions\Error;

class Upload extends \Espo\Core\Upgrades\Actions\Base\Upload
{
    /**
     * Check dependencies.
     *
     * @param array<string, string[]|string> $dependencyList
     * @throws Error
     */
    protected function checkDependencies($dependencyList): bool
    {
        return $this->getHelper()->checkDependencies($dependencyList);
    }
}
