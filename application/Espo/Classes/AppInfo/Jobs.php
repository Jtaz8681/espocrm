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
use Espo\Core\Utils\ClassFinder;
use Espo\Core\Job\MetadataProvider;
use Espo\Tools\ConsoleAppInfo\InfoProvider;

class Jobs implements InfoProvider
{

    public function __construct(
        private ClassFinder $classFinder,
        private MetadataProvider $metadataProvider,
    ) {}

    public function get(Params $params): string
    {
        $result = "Available jobs:\n\n";

        $list = array_map(
            function ($item) {
                return ' ' . $item;
            },
            array_unique(
                array_merge(
                    array_keys($this->classFinder->getMap('Jobs')),
                    $this->metadataProvider->getScheduledJobNameList()
                )
            )
        );

        asort($list);

        return $result . implode("\n", $list) . "\n";
    }
}
