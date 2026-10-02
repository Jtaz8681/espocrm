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

namespace Espo\Core\Log\Handler;

use Espo\Core\Api\Request;
use Espo\Core\ApplicationState;
use Espo\Core\ORM\EntityManagerProxy;
use Espo\Entities\AppLogRecord;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

class DatabaseHandler implements HandlerInterface
{
    public function __construct(
        private Level $level,
        private EntityManagerProxy $entityManager,
        private ApplicationState $applicationState
    ) {}

    public function isHandling(LogRecord $record): bool
    {
        if (!$this->applicationState->hasUser()) {
            return false;
        }

        if ($record->context['isSql'] ?? false) {
            return false;
        }

        return $record->level->value >= $this->level->value;
    }

    public function handle(LogRecord $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        try {
            $logRecord = $this->entityManager->getRDBRepositoryByClass(AppLogRecord::class)->getNew();

            $level = ucfirst($record->level->toPsrLogLevel());
            $message = $this->interpolate($record, $record->message);

            $logRecord
                ->setLevel($level)
                ->setMessage($message);

            $this->setException($record, $logRecord);
            $this->setRequest($record, $logRecord);

            $this->entityManager->saveEntity($logRecord);
        } catch (Throwable) {
            // Nowhere to log.
        }

        return false;
    }

    private function interpolate(LogRecord $record, string $line): string
    {
        $replace = [];

        foreach ($record->context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($line, $replace);
    }

    /**
     * @param LogRecord[] $records
     */
    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {}

    private function setException(LogRecord $record, AppLogRecord $logRecord): void
    {
        $exception = $record->context['exception'] ?? null;

        if (!$exception instanceof Throwable) {
            return;
        }

        $logRecord
            ->setExceptionClass(get_class($exception))
            ->setFile($exception->getFile())
            ->setLine($exception->getLine())
            ->setCode($exception->getCode());
    }

    private function setRequest(LogRecord $record, AppLogRecord $logRecord): void
    {
        $request = $record->context['request'] ?? null;

        if (!$request instanceof Request) {
            return;
        }

        $logRecord
            ->setRequestMethod($request->getMethod())
            ->setRequestResourcePath($request->getResourcePath());
    }
}
