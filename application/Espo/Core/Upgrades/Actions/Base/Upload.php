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

class Upload extends Base
{
    /**
     * Upload an upgrade/extension package.
     *
     * @param string $data
     * @return string ID of upgrade/extension process.
     * @throws Error
     */
    public function run(mixed $data): string
    {
        $processId = $this->createProcessId();

        $this->getLog()->debug("Installation process [$processId]: start upload the package.");

        $this->initialize();
        $this->beforeRunAction();

        $packageArchivePath = $this->getPackagePath(true);

        $contents = null;

        if (!empty($data)) {
            [, $contents] = explode(',', $data);

            $contents = base64_decode($contents);
        }

        $res = $this->getFileManager()->putContents($packageArchivePath, $contents);

        if ($res === false) {
            throw new Error('Could not upload the package.');
        }

        $this->unzipArchive();
        $this->isAcceptable();
        $this->afterRunAction();
        $this->finalize();

        $this->getLog()->debug("Installation process [$processId]: end upload the package.");

        return $processId;
    }
}
