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

namespace Espo\Core\Utils;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use DateTimeZone;
use Stringable;

class Log implements LoggerInterface
{
    public const LEVEL_DEBUG = LogLevel::DEBUG;
    public const LEVEL_NOTICE = LogLevel::NOTICE;
    public const LEVEL_WARNING = LogLevel::WARNING;
    public const LEVEL_ERROR = LogLevel::ERROR;

    private Logger $logger;

    /**
     * @param list<HandlerInterface> $handlers
     * @param callable[] $processors
     * @param ?DateTimeZone $timezone
     */
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        $this->logger = new Logger($name, $handlers, $processors, $timezone);
    }

    public function pushHandler(HandlerInterface $handler): self
    {
        $this->logger->pushHandler($handler);

        return $this;
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @param mixed $level
     * @param mixed[] $context
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
