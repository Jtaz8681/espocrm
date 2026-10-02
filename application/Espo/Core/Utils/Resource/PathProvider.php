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

namespace Espo\Core\Utils\Resource;

use Espo\Core\Utils\Module\PathProvider as ModulePathProvider;

class PathProvider
{
    public function __construct(private ModulePathProvider $provider)
    {}

    public function getCore(): string
    {
        return $this->provider->getCore() . 'Resources/';
    }

    public function getCustom(): string
    {
        return $this->provider->getCustom() . 'Resources/';
    }

    public function getModule(string $moduleName): string
    {
        return $this->provider->getModule($moduleName) . 'Resources/';
    }
}
