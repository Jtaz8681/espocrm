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

namespace Espo\Core\Upgrades\Migrations\V10_0;

use Espo\Core\Upgrades\Migration\Script;
use Espo\Entities\Extension;
use Espo\ORM\EntityManager;
use RuntimeException;

class Prepare implements Script
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function run(): void
    {
        $this->processCheckExtensions();
    }

    private function processCheckExtensions(): void
    {
        $errorMessageList = [];

        $this->processCheckExtension('Advanced Pack', '3.13.0', $errorMessageList);
        $this->processCheckExtension('Real Estate', '1.8.5', $errorMessageList);
        $this->processCheckExtension('VoIP Integration', '2.8.0', $errorMessageList);

        if (!count($errorMessageList)) {
            return;
        }

        $message = implode("\n\n", $errorMessageList);

        throw new RuntimeException($message);
    }

    /**
     * @param string[] $errorMessageList
     */
    private function processCheckExtension(string $name, string $minVersion, array &$errorMessageList): void
    {
        $extension = $this->entityManager
            ->getRDBRepositoryByClass(Extension::class)
            ->where([
                'name' => $name,
                'isInstalled' => true,
            ])
            ->findOne();

        if (!$extension) {
            return;
        }

        $version = $extension->getVersion();

        if (version_compare($version, $minVersion, '>=')) {
            return;
        }

        $message =
            "BugZyro 10.0 is not compatible with '$name' extension of versions lower than $minVersion. " .
            "You need to upgrade the extension.";

        $errorMessageList[] = $message;
    }
}
