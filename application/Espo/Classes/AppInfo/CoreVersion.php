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

namespace Espo\Classes\AppInfo;

use Espo\Core\Console\Command\Params;
use Espo\Core\Upgrades\Migration\VersionDataProvider;
use Espo\Tools\ConsoleAppInfo\InfoProvider;

/**
 * @noinspection PhpUnused
 */
class CoreVersion implements InfoProvider
{
    public function __construct(
        private VersionDataProvider $versionDataProvider,
    ) {}

    public function get(Params $params): string
    {
        return $this->versionDataProvider->getTargetVersion();
    }
}
