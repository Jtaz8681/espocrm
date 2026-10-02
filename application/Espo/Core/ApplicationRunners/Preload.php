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
use Espo\Core\Utils\Preload as PreloadUtil;

use Throwable;

/**
 * Runs a preload.
 *
 * @see https://www.php.net/manual/en/opcache.preloading.php
 */
class Preload implements Runner
{
    use Cli;

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $preload = new PreloadUtil();

        try {
            $preload->process();
        } catch (Throwable $e) {
            $this->processException($e);

            throw $e;
        }

        $count = $preload->getCount();

        echo "Success." . PHP_EOL;
        echo "Files loaded: " . $count . "." . PHP_EOL;
    }

    protected function processException(Throwable $e): void
    {
        echo "Error occurred." . PHP_EOL;

        $msg = $e->getMessage();

        if ($msg) {
            echo "Message: $msg" . PHP_EOL;
        }

        $file = $e->getFile();

        if ($file) {
            echo "File: $file" . PHP_EOL;
        }

        echo "Line: " . $e->getLine() . PHP_EOL;
    }
}
