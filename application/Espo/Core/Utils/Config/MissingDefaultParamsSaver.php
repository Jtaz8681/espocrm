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

use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;

use RuntimeException;

class MissingDefaultParamsSaver
{
    private string $defaultConfigPath = 'application/Espo/Resources/defaults/config.php';

    public function __construct(
        private Config $config,
        private ConfigWriter $configWriter,
        private FileManager $fileManager
    ) {}

    public function process(): void
    {
        $data = $this->fileManager->getPhpSafeContents($this->defaultConfigPath);

        if (!is_array($data)) {
            throw new RuntimeException();
        }

        /** @var array<string, mixed> $data */

        $newData = [];

        foreach ($data as $param => $value) {
            if ($this->config->has($param)) {
                continue;
            }

            $newData[$param] = $value;
        }

        if (!count($newData)) {
            return;
        }

        $this->configWriter->setMultiple($newData);
        $this->configWriter->save();
    }
}
