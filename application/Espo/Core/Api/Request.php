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

use Psr\Http\Message\UriInterface;

use stdClass;

/**
 * Representation of an HTTP request.
 */
interface Request
{
    /**
     * Whether a query parameter is set.
     */
    public function hasQueryParam(string $name): bool;

    /**
     * Get a query parameter.
     */
    public function getQueryParam(string $name): ?string;

    /**
     * Get all query parameters.
     *
     * @return array<string, string|array<scalar, mixed>>
     */
    public function getQueryParams(): array;

    /**
     * Whether a route parameter is set.
     */
    public function hasRouteParam(string $name): bool;

    /**
     * Get a route parameter.
     */
    public function getRouteParam(string $name): ?string;

    /**
     * Get all route parameters.
     *
     * @return array<string, string>
     */
    public function getRouteParams(): array;

    /**
     * Get a header value. Multiple values will be concatenated with a comma.
     */
    public function getHeader(string $name): ?string;

    /**
     * Whether a header is set.
     */
    public function hasHeader(string $name): bool;

    /**
     * Get a header values as an array.
     *
     * @return string[]
     */
    public function getHeaderAsArray(string $name): array;

    /**
     * Get a request method.
     */
    public function getMethod(): string;

    /**
     * Get Uri.
     */
    public function getUri(): UriInterface;

    /**
     * Get a relative path of a request (w/o base path).
     */
    public function getResourcePath(): string;

    /**
     * Get body contents.
     */
    public function getBodyContents(): ?string;

    /**
     * Get a parsed body. If JSON array is passed, then will be converted to `{"list": ARRAY}`.
     */
    public function getParsedBody(): stdClass;

    /**
     * Get a cookie param value.
     */
    public function getCookieParam(string $name): ?string;

    /**
     * Get a server param value.
     *
     * @return mixed
     */
    public function getServerParam(string $name);
}
