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

namespace Espo\Core\ApplicationRunners;

use Espo\Core\Application\Runner;
use Espo\Core\DataManager;
use Espo\Core\Utils\Log;
use Exception;

/**
 * Rebuilds an application.
 */
class Rebuild implements Runner
{
    use Cli;

    public function __construct(private DataManager $dataManager, private Log $log)
    {}

    public function run(): void
    {
        try {
            $this->dataManager->rebuild();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";

            $this->log->error('Rebuild: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
            ]);

            exit(1);
        }
    }
}
