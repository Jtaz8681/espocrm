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

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;

/**
 * @internal
 */
class WrapperException extends Error
{
    private Conflict|BadRequest|Forbidden $wrappedException;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(Conflict|BadRequest|Forbidden $wrappedException): self
    {
        $created = new self();
        $created->wrappedException = $wrappedException;

        return $created;
    }

    public function getWrappedException(): Conflict|BadRequest|Forbidden
    {
        return $this->wrappedException;
    }
}
