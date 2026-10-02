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

namespace Espo\Core\Loaders;

use Espo\Core\Container\Loader;
use Espo\Core\Log\LogLoader;
use Espo\Core\Utils\Log as LogService;

class Log implements Loader
{
    public function __construct(private LogLoader $logLoader)
    {}

    public function load(): LogService
    {
        $log = $this->logLoader->load();

        // @todo Remove in future.
        $GLOBALS['log'] = $log;

        return $log;
    }
}
