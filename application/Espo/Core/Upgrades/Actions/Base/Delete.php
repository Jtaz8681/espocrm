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

namespace Espo\Core\Upgrades\Actions\Base;

use Espo\Core\Exceptions\Error;
use Espo\Core\Upgrades\Actions\Base;

class Delete extends Base
{
    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function run(mixed $data): mixed
    {
        $processId = $data['id'];

        $this->getLog()->debug('Delete package process ['.$processId.']: start run.');

        if (empty($processId)) {
            throw new Error('Delete package package ID was not specified.');
        }

        $this->initialize();
        $this->setProcessId($processId);

        if (isset($data['parentProcessId'])) {
            $this->setParentProcessId($data['parentProcessId']);
        }

        $this->beforeRunAction();
        /* delete a package */
        $this->deletePackage();
        $this->afterRunAction();
        $this->finalize();

        $this->getLog()->debug('Delete package process ['.$processId.']: end run.');

        return null;
    }

    /**
     * @throws Error
     */
    protected function deletePackage(): bool
    {
        $packageArchivePath = $this->getPackagePath(true);

        return $this->getFileManager()->removeFile($packageArchivePath);
    }
}
