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

use Espo\Core\Utils\File\Manager;
use const SORT_STRING;

class StepsProvider
{
    private string $dir = 'application/Espo/Core/Upgrades/Migrations';

    public function __construct(
        private Manager $fileManager
    ) {}

    /**
     * @return string[]
     */
    public function getPrepare(): array
    {
        return $this->get('Prepare');
    }

    /**
     * @return string[]
     */
    public function getAfterUpgrade(): array
    {
        return $this->get('AfterUpgrade');
    }

    /**
     * @return string[]
     */
    private function get(string $name): array
    {
        $list = $this->fileManager->getDirList($this->dir);

        $list = array_filter($list, function ($item) use ($name) {
            $dir = $this->dir . '/' . $item;

            return $this->fileManager->isFile("$dir/$name.php");
        });

        $list = array_values($list);
        $list = array_map(fn ($item) => substr(str_replace('_', '.', $item), 1), $list);

        sort($list, SORT_STRING);

        return $list;
    }
}
