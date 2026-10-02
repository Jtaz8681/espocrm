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

namespace Espo\Core\Session;

use const PHP_SESSION_NONE;

/**
 * Do not use directly. Require the Session interface instead.
 *
 * @internal
 */
class DefaultSession implements Session
{
    public function __construct(
        ?string $cacheLimiter = null,
        ?int $cacheExpire = null,
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            if ($cacheLimiter !== null) {
                session_cache_limiter($cacheLimiter);
            }

            if ($cacheExpire !== null) {
                session_cache_expire($cacheExpire);
            }

            session_start();
        }
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, mixed $value): Session
    {
        $_SESSION[$key] = $value;

        return $this;
    }

    public function clear(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clearAll(): void
    {
        session_unset();
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }
}
