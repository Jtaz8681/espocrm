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

namespace Espo\Core\Exceptions;

use Espo\Core\Exceptions\Error\Body;
use Exception;
use Throwable;

/**
 * A conflict exception. Main purpose is for the 409 Conflict HTTP error.
 * If uncaught within an API request, the message will be printed to the X-Status-Reason header.
 */
class Conflict extends Exception implements HasBody
{
    /** @var int */
    protected $code = 409;
    private ?string $body = null;

    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create with a body (supposed to be sent to the frontend).
     * Body object is supported since v8.1.
     */
    public static function createWithBody(string $reason, string|Body $body): static
    {
        if ($body instanceof Body) {
            $body = $body->encode();
        }

        $exception = new static($reason);
        $exception->body = $body;

        return $exception;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }
}
