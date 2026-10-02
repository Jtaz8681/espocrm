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

use Espo\Core\Api\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use Throwable;

class DefaultFormatter extends LineFormatter
{
    private const string LINE_FORMAT = "[%datetime%] %level_name%: %code% %message% %request%\n";
    private const string DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private bool $includeTraces = false,
    ) {
        parent::__construct(
            format: self::LINE_FORMAT,
            dateFormat: self::DATE_FORMAT,
            ignoreEmptyContextAndExtra: true,
            includeStacktraces: $this->includeTraces,
        );
    }

    public function format(LogRecord $record): string
    {
        $line = parent::format($record);

        $line = $this->interpolate($record, $line);
        $line = $this->addCode($record, $line);
        $line = $this->addRequest($record, $line);
        $line = $this->addException($record, $line);

        return trim($line) . "\n";
    }

    private function addCode(LogRecord $record, string $line): string
    {
        $exception = $record->context['exception'] ?? null;

        if (!$exception instanceof Throwable) {
            return str_replace('%code% ', '', $line);
        }

        $codePart = "({$exception->getCode()})";

        return str_replace('%code% ', $codePart . ' ', $line);
    }

    private function addException(LogRecord $record, string $line): string
    {
        $exception = $record->context['exception'] ?? null;

        if (!$exception instanceof Throwable) {
            return str_replace('%exception%', '', $line);
        }

        $line .= $this->normalizeException($exception);

        return $line;
    }

    private function addRequest(LogRecord $record, string $line): string
    {
        $request = $record->context['request'] ?? null;

        if (!$request instanceof Request) {
            return str_replace('%request%', '', $line);
        }

        $requestPart = ":: {$request->getMethod()} {$request->getResourcePath()}";

        return str_replace('%request%', $requestPart, $line);
    }

    private function interpolate(LogRecord $record, mixed $line): string
    {
        $replace = [];

        foreach ($record->context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($line, $replace);
    }
}
