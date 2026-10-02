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

namespace Espo\Core\Api;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\HasBody;
use Espo\Core\Exceptions\HasLogLevel;
use Espo\Core\Exceptions\HasLogMessage;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Log;

use Espo\ORM\Exceptions\ValidationException;
use LogicException;
use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;

/**
 * Processes an error output. If an exception occurred, it will be passed to here.
 */
class ErrorOutput
{
    /** @var array<int, string> */
    private $errorDescriptions = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Page Not Found',
        409 => 'Conflict',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    /** @var int[] */
    private $allowedStatusCodeList = [
        200,
        201,
        400,
        401,
        403,
        404,
        409,
        500,
        503,
    ];

    /** @var class-string<Throwable>[] */
    private array $printStatusReasonExceptionClassNameList = [
        Error::class,
        Forbidden::class,
        Conflict::class,
        BadRequest::class,
        NotFound::class,
    ];

    /** @var array<class-string<Throwable>, int> */
    private array $statusCodeMap = [
        ValidationException::class => 409,
    ];

    public function __construct(private Log $log)
    {}

    public function process(
        Request $request,
        Response $response,
        Throwable $exception,
        ?string $route = null
    ): void {

        $this->processInternal($request, $response, $exception, $route);
    }

    public function processWithBodyPrinting(
        Request $request,
        Response $response,
        Throwable $exception,
        ?string $route = null
    ): void {

        $this->processInternal($request, $response, $exception, $route, true);
    }

    private function processInternal(
        Request $request,
        Response $response,
        Throwable $exception,
        ?string $route = null,
        bool $toPrintBody = false
    ): void {

        [$message, $logMessage] = $this->prepareMessages($exception);

        if ($route) {
            $this->processRoute($route, $request, $exception);
        }

        $level = $this->getLevel($exception);

        $this->log->log($level, $logMessage, [
            'exception' => $exception,
            'request' => $request,
        ]);

        $statusCode = $this->getStatusCode($exception);

        $response->setStatus($statusCode);

        if ($this->toPrintExceptionStatusReason($exception)) {
            $response->setHeader('X-Status-Reason', $this->stripInvalidCharactersFromHeaderValue($message));
        }

        $this->printBody(
            exception: $exception,
            response: $response,
            toPrintBody: $toPrintBody,
            statusCode: $statusCode,
            message: $message,
        );
    }

    private function exceptionHasBody(Throwable $exception): bool
    {
        if (!$exception instanceof HasBody) {
            return false;
        }

        $exceptionBody = $exception->getBody();

        return $exceptionBody !== null;
    }

    private function getCodeDescription(int $statusCode): ?string
    {
        if (isset($this->errorDescriptions[$statusCode])) {
            return $this->errorDescriptions[$statusCode];
        }

        return null;
    }

    private static function generateErrorBody(string $header, string $text): string
    {
        $body = "<h1>" . $header . "</h1>";
        $body .= $text;

        return $body;
    }

    private function stripInvalidCharactersFromHeaderValue(string $value): string
    {
        $pattern = "/[^ \t\x21-\x7E\x80-\xFF]/";

        /** @var string */
        return preg_replace($pattern, ' ', $value);
    }

    private function processRoute(string $route, Request $request, Throwable $exception): void
    {
        $message = $exception->getMessage();

        if ($exception->getPrevious() && $exception->getPrevious()->getMessage()) {
            $message .= " " . $exception->getPrevious()->getMessage();
        }

        $statusCode = $exception->getCode();

        $routeParams = $request->getRouteParams();

        $logMessage = "API ($statusCode) ";

        $logMessageItemList = [];

        if ($message) {
            $logMessageItemList[] = $message;
        }

        $logMessageItemList[] = $request->getMethod() . ' ' . $request->getResourcePath();

        $logMessageItemList[] = "Route pattern: " . $route;

        if (!empty($routeParams)) {
            $logMessageItemList[] = "Route params: " . print_r($routeParams, true);
        }

        $logMessage .= implode("; ", $logMessageItemList);

        $this->log->debug($logMessage);
    }

    private function toPrintExceptionStatusReason(Throwable $exception): bool
    {
        foreach ($this->printStatusReasonExceptionClassNameList as $clasName) {

            if ($exception instanceof ($clasName)) {
                return true;
            }
        }

        return false;
    }

    private function getLevel(Throwable $exception): string
    {
        if ($exception instanceof HasLogLevel) {
            return $exception->getLogLevel();
        }

        if ($exception instanceof LogicException) {
            return LogLevel::ALERT;
        }

        if ($exception instanceof RuntimeException) {
            return LogLevel::CRITICAL;
        }

        return LogLevel::ERROR;
    }

    private function printBody(
        Throwable $exception,
        Response $response,
        bool $toPrintBody,
        int $statusCode,
        string $message,
    ): void {

        if ($exception instanceof HasBody && $this->exceptionHasBody($exception)) {
            $response->writeBody($exception->getBody() ?? '');

            $toPrintBody = false;
        }

        if (!$toPrintBody) {
            return;
        }

        $codeDescription = $this->getCodeDescription($statusCode);

        $statusText = isset($codeDescription) ?
            $statusCode . ' ' . $codeDescription :
            'HTTP ' . $statusCode;

        if ($message) {
            $message = htmlspecialchars($message);
        }

        $response->writeBody(self::generateErrorBody($statusText, $message));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function prepareMessages(Throwable $exception): array
    {
        $message = $exception->getMessage();
        $logMessage = $message;

        if ($exception->getPrevious() && $exception->getPrevious()->getMessage()) {
            $logMessage .= " " . $exception->getPrevious()->getMessage();
        }

        if ($exception instanceof HasLogMessage) {
            $message = $exception->getLogMessage();
            $logMessage = $message;
        }

        return [$message, $logMessage];
    }

    /**
     * @return int
     */
    private function getStatusCode(Throwable $exception): int
    {
        $statusCode = $this->statusCodeMap[$exception::class] ?? $exception->getCode();

        if (!in_array($statusCode, $this->allowedStatusCodeList)) {
            $statusCode = 500;
        }

        return $statusCode;
    }
}
