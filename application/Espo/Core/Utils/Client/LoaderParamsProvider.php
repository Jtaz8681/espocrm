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

use Espo\Core\Utils\Metadata;

class LoaderParamsProvider
{
    public function __construct(
        private Metadata $metadata
    ) {}

    public function getLibsConfig(): object
    {
        return (object) $this->metadata->get(['app', 'jsLibs'], []);
    }

    public function getAliasMap(): object
    {
        $map = (object) [];

        /** @var array<string, array<string, mixed>> $libs */
        $libs = $this->metadata->get(['app', 'jsLibs'], []);

        foreach ($libs as $name => $item) {
            /** @var ?string[] $aliases */
            $aliases = $item['aliases'] ?? null;

            $map->$name = 'lib!' . $name;

            if ($aliases) {
                foreach ($aliases as $alias) {
                    $map->$alias = 'lib!' . $name;
                }
            }
        }

        return $map;
    }
}
