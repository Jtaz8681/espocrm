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

namespace Espo\Core\Console\Commands;

use Espo\Core\Application;
use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\Upgrades\UpgradeManager;

use Exception;

/**
 * @noinspection PhpUnused
 */
class UpgradeStep implements Command
{
    public function __construct() {}

    public function run(Params $params, IO $io): void
    {
        $options = $params->getOptions();

        if (empty($options['step'])) {
            echo "Step is not specified.\n";

            return;
        }

        if (empty($options['id'])) {
            echo "Upgrade ID is not specified.\n";

            return;
        }

        $stepName = $options['step'];
        $upgradeId = $options['id'];

        $this->runUpgradeStep($stepName, ['id' => $upgradeId]);

        echo "true";
    }

    /**
     * @param array<string, mixed> $params
     */
    private function runUpgradeStep(string $stepName, array $params): void
    {
        $app = new Application();

        $app->setupSystemUser();

        $upgradeManager = new UpgradeManager($app->getContainer());

        try {
            $upgradeManager->runInstallStep($stepName, $params);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
