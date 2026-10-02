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

namespace Espo\Core\Utils\Config;


use Espo\Core\Utils\File\Manager;

/**
 * @internal
 * @since 10.0.0
 */
class Populator
{
    private string $configFile = 'data/config.php';

    public function __construct(
        private Manager $fileManager,
        private MissingDefaultParamsSaver $missingDefaultParamsSaver,
    ) {}

    public function populate(): void
    {
        if (!$this->fileManager->exists($this->configFile)) {
            $this->fileManager->putPhpContents($this->configFile, []);
        }

        $this->missingDefaultParamsSaver->process();
    }
}
