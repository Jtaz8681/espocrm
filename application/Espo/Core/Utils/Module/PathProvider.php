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

namespace Espo\Core\Utils\Module;

use Espo\Core\Utils\Module;

class PathProvider
{
    private string $corePath = 'application/Espo/';
    private string $customPath = 'custom/Espo/Custom/';

    public function __construct(private Module $module)
    {}

    public function getCore(): string
    {
        return $this->corePath;
    }

    public function getCustom(): string
    {
        return $this->customPath;
    }

    public function getModule(string $moduleName): string
    {
        return $this->module->getModulePath($moduleName) . '/';
    }
}
