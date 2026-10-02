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

/**
 * An authentication result.
 */
class AuthResult
{
    private bool $isResolved = false;
    private bool $isResolvedUseNoAuth = false;

    public static function createResolved(): self
    {
        $obj = new self();

        $obj->isResolved = true;

        return $obj;
    }

    public static function createResolvedUseNoAuth(): self
    {
        $obj = new self();

        $obj->isResolved = true;
        $obj->isResolvedUseNoAuth = true;

        return $obj;
    }

    public static function createNotResolved(): self
    {
        return new self();
    }

    /**
     * Logged in successfully.
     */
    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    /**
     * No need to log in.
     */
    public function isResolvedUseNoAuth(): bool
    {
        return $this->isResolvedUseNoAuth;
    }
}
