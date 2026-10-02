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

namespace Espo\Tools\ExportCustom;

use RuntimeException;

class Data
{
    /**
     * @param string[] $customEntityTypeList
     */
    public function __construct(
        public string $folder,
        public array $customEntityTypeList,
        private string $module
    ) {

        if (!preg_match('/^[A-Za-z0-9.\-]+$/', $folder)) {
            throw new RuntimeException("Bad folder.");
        }
    }

    public function getDir(): string
    {
        return 'data/tmp/' . $this->folder;
    }

    public function getDestDir(): string
    {
        return $this->getDir() . '/files/custom/Espo/Modules/' . basename($this->module);
    }
}
