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

namespace Espo\Core\HttpClient;

use Espo\Core\HttpClient\Exceptions\ConnectException;
use Espo\Core\HttpClient\Exceptions\TooManyRedirectsException;
use Psr\Http\Message\ResponseInterface;

/**
 * @since 10.0.0
 * @noinspection PhpUnused
 */
class PromiseUtil
{
    /**
     * @param array<int|string, Promise> $promises
     * @return array<int|string, ResponseInterface>
     * @throws ConnectException
     * @throws TooManyRedirectsException
     */
    public static function unwrap(array $promises): array
    {
        return array_map(fn ($promise) => $promise->wait(), $promises);
    }
}
