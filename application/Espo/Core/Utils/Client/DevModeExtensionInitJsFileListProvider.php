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

namespace Espo\Core\Utils\Client;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Module;
use Espo\Core\Utils\Util;

/**
 * Allows bundled extensions to work when the system is in the developer mode.
 */
class DevModeExtensionInitJsFileListProvider
{
    public function __construct(
        private Module $module,
        private FileManager $fileManager,
        private Config $config,
    ) {}

    /**
     * @return string[]
     */
    public function get(): array
    {
        $developedModule = $this->config->get('developedModule');

        if (!$developedModule) {
            return [];
        }

        $output = [];

        foreach ($this->getBundledModuleList() as $module) {
            if ($module === $developedModule) {
                continue;
            }

            $file = "client/custom/modules/$module/lib/init.js";

            if ($this->fileManager->exists($file)) {
                $output[] = $file;
            }
        }

        return $output;
    }

    /**
     * @return string[]
     */
    private function getBundledModuleList(): array
    {
        $modules = array_values(array_filter(
            $this->module->getList(),
            fn ($item) => $this->module->get([$item, 'bundled'])
        ));

        return array_map(
            fn ($item) => Util::fromCamelCase($item, '-'),
            $modules
        );
    }
}
