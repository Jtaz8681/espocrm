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

use Espo\Core\DataManager;
use Espo\Core\Exceptions\Error;
use Espo\Core\InjectableFactory;
use RuntimeException;

class AfterUpgradeRunner
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private DataManager $dataManager
    ) {}

    public function run(string $step): void
    {
        $dir = 'V' . str_replace('.', '_', $step);

        $className = "Espo\\Core\\Upgrades\\Migrations\\$dir\\AfterUpgrade";

        if (!class_exists($className)) {
            throw new RuntimeException("No after-upgrade script $step.");
        }

        try {
            $this->dataManager->rebuild();
        } catch (Error $e) {
            throw new RuntimeException("Error while rebuild: " . $e->getMessage());
        }

        /** @var Script $script */
        $script = $this->injectableFactory->createWith($className, ['isUpgrade' => false]);
        $script->run();

        try {
            $this->dataManager->rebuild();
        } catch (Error $e) {
            throw new RuntimeException("Error while rebuild: " . $e->getMessage());
        }
    }
}
