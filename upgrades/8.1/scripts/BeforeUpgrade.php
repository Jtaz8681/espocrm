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

use Espo\Core\Exceptions\Error;
use Espo\Core\Container;
use Espo\Core\Utils\Config;

/** @noinspection PhpMultipleClassDeclarationsInspection */
class BeforeUpgrade
{
    /**
     * @throws Error
     */
    public function run(Container $container): void
    {
        $this->checkLogHandlers($container);
    }

    /**
     * @throws Error
     */
    private function checkLogHandlers(Container $container): void
    {
        $config = $container->getByClass(Config::class);

        if (!$config->get('logger.handlerList')) {
            return;
        }

        $msg = "You need to remove logger.handlerList from the `data/config-internal.php` before upgrading. " .
            "In EspoCRM v8.1, Monolog library was updated, custom log handlers may be incompatible. ".
            "You will be able to return the handlers back after the upgrade.";

        throw new Error($msg);
    }
}
