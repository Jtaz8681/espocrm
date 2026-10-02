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

/**
 * A bad argument value.
 */
class BadArgumentValue extends Error
{
    private ?int $position = null;
    private ?string $logMessage = null;

    /**
     * Create.
     *
     * @param int $position An argument position.
     */
    public static function create(int $position, ?string $message = null): self
    {
        $obj = new self();
        $obj->position = $position;
        $obj->logMessage = $message;

        return $obj;
    }

    public function getLogMessage(): string
    {
        $position = (string) ($this->position ?? '?');

        $message = "Bad argument value at position $position.";

        if ($this->logMessage) {
            $message .= " " . $this->logMessage;
        }

        return $message;
    }
}
