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

namespace Espo\Core\Log;

use Espo\Core\Log\Handler\EspoRotatingFileHandler;
use Espo\Core\Utils\Config;

use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Logger;

class EspoRotatingFileHandlerLoader implements HandlerLoader
{
    public function __construct(
        private readonly Config $config
    ) {}

    public function load(array $params): HandlerInterface
    {
        $filename = $params['filename'] ?? 'data/logs/espo.log';
        $levelCode = $params['level'] ?? Level::Notice->value;
        $level = Logger::toMonologLevel($levelCode);

        return new EspoRotatingFileHandler($this->config, $filename, 0, $level);
    }
}
