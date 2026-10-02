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

namespace Espo\Core\Formula\Exceptions;

use Throwable;

class SyntaxError extends Error
{
    /**
     * @var ?string
     */
    private $shortMessage = null;

    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(string $message, ?string $shortMessage = null): self
    {
        $obj = new static($message);
        $obj->shortMessage = $shortMessage;

        return $obj;
    }

    public function getShortMessage(): ?string
    {
        return $this->shortMessage ?? $this->getMessage();
    }
}
