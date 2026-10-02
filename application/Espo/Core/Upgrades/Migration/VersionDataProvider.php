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

namespace Espo\Core\Upgrades\Migration;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager;
use RuntimeException;

class VersionDataProvider
{
    private string $defaultConfigPath = 'application/Espo/Resources/defaults/config.php';

    public function __construct(
        private Manager $fileManager,
        private Config\SystemConfig $systemConfig,
    ) {}

    public function getPreviousVersion(): string
    {
        $version = $this->systemConfig->getVersion();

        if (!$version) {
            throw new RuntimeException("No or bad 'version' in config.");
        }

        return $version;
    }

    public function getTargetVersion(): string
    {
        $data = $this->fileManager->getPhpContents($this->defaultConfigPath);

        if (!is_array($data)) {
            throw new RuntimeException("No default config.");
        }

        $version = $data['version'] ?? null;

        if (!$version || !is_string($version)) {
            throw new RuntimeException("No or bad 'version' parameter in default config.");
        }

        return $version;
    }
}
