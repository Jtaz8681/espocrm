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

use Espo\Core\Application\ApplicationParams;
use Espo\Core\ApplicationState;
use Espo\Core\Log\Handler\DatabaseHandler;
use Espo\Core\Log\Handler\EspoFileHandler;
use Espo\Core\Log\Handler\EspoRotatingFileHandler;
use Espo\Core\ORM\EntityManagerProxy;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;

use Monolog\ErrorHandler as MonologErrorHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

class LogLoader
{
    private const PATH = 'data/logs/espo.log';

    private const MAX_FILE_NUMBER = 30;
    private const DEFAULT_LEVEL = 'WARNING';

    public function __construct(
        private readonly Config $config,
        private readonly HandlerListLoader $handlerListLoader,
        private readonly EntityManagerProxy $entityManagerProxy,
        private readonly ApplicationState $applicationState,
        private readonly ApplicationParams $applicationParams,
    ) {}

    public function load(): Log
    {
        $log = new Log('Espo');

        $handlerDataList = $this->config->get('logger.handlerList') ?? null;

        if ($handlerDataList) {
            $level = $this->config->get('logger.level');

            $handlerList = $this->handlerListLoader->load($handlerDataList, $level);
        } else {
            $handlerList = [$this->createDefaultHandler()];
        }

        if ($this->config->get('logger.databaseHandler')) {
            $handlerList[] = $this->createDatabaseHandler();
        }

        foreach ($handlerList as $handler) {
            $log->pushHandler($handler);
        }

        if (!$this->applicationParams->noErrorHandler) {
            $errorHandler = new MonologErrorHandler($log);

            $errorHandler->registerExceptionHandler([], false);
            $errorHandler->registerErrorHandler([], false);
        }

        return $log;
    }

    private function createDefaultHandler(): HandlerInterface
    {
        $path = $this->config->get('logger.path') ?? self::PATH;
        $level = $this->config->get('logger.level') ?? self::DEFAULT_LEVEL;
        $rotation = $this->config->get('logger.rotation') ?? true;

        $levelCode = Logger::toMonologLevel($level);

        if ($rotation) {
            $maxFileNumber = $this->config->get('logger.maxFileNumber') ?? self::MAX_FILE_NUMBER;

            $handler = new EspoRotatingFileHandler($this->config, $path, $maxFileNumber, $levelCode, true);
        } else {
            $handler = new EspoFileHandler($this->config, $path, $levelCode, true);
        }

        $formatter = new DefaultFormatter($this->printTrace());

        $handler->setFormatter($formatter);

        return $handler;
    }

    private function printTrace(): bool
    {
        return (bool) $this->config->get('logger.printTrace');
    }

    private function createDatabaseHandler(): HandlerInterface
    {
        $rawLevel = $this->config->get('logger.databaseHandlerLevel') ??
            $this->config->get('logger.level') ??
            self::DEFAULT_LEVEL;

        $level = Logger::toMonologLevel($rawLevel);

        return new DatabaseHandler($level, $this->entityManagerProxy, $this->applicationState);
    }
}
