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
use Espo\Core\Container as ContainerService;
use Espo\Core\Utils\Metadata;
use Espo\Tools\ConsoleAppInfo\InfoProvider;

class Container implements InfoProvider
{
    public function __construct(private ContainerService $container, private Metadata $metadata)
    {}

    public function get(Params $params): string
    {
        $nameOnly = $params->hasFlag('nameOnly');

        $result = '';

        $serviceList = [
            'injectableFactory',
            'config',
            'log',
            'fileManager',
            'dataManager',
            'metadata',
            'user',
        ];

        /** @var string[] $fileList */
        $fileList = scandir('application/Espo/Core/Loaders');

        if (file_exists('custom/Espo/Custom/Core/Loaders')) {
            $fileList = array_merge($fileList, scandir('custom/Espo/Custom/Core/Loaders') ?: []);
        }

        foreach ($fileList as $file) {
            if (substr($file, -4) === '.php') {
                $name = lcfirst(substr($file, 0, -4));

                if (!in_array($name, $serviceList) && $this->container->has($name)) {
                    $serviceList[] = $name;
                }
            }
        }

        foreach ($this->metadata->get(['app', 'containerServices']) ?? [] as $name => $data) {
            if (!in_array($name, $serviceList)) {
                $serviceList[] = $name;
            }
        }

        sort($serviceList);

        if ($nameOnly) {
            foreach ($serviceList as $name) {
                $result .= $name . "\n";
            }

            return $result;
        }

        foreach ($serviceList as $name) {
            $result .= $name . "\n";

            $obj = $this->container->get($name);
            $result .= get_class($obj) . "\n";

            $result .= "\n";
        }

        return $result;
    }
}
