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

use Espo\Core\Api\Request as ApiRequest;

use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\UriFactory;

use stdClass;

/**
 * An empty stub for Request.
 */
class RequestNull implements ApiRequest
{
    public function hasQueryParam(string $name): bool
    {
        return false;
    }

    /**
     * @return null
     * @noinspection PhpDocSignatureInspection
     */
    public function getQueryParam(string $name): ?string
    {
        return null;
    }

    public function getQueryParams(): array
    {
        return [];
    }

    public function hasRouteParam(string $name): bool
    {
        return false;
    }

    public function getRouteParam(string $name): ?string
    {
        return null;
    }

    public function getRouteParams(): array
    {
        return [];
    }

    public function getHeader(string $name): ?string
    {
        return null;
    }

    public function hasHeader(string $name): bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getHeaderAsArray(string $name): array
    {
        return [];
    }

    public function getMethod(): string
    {
        return '';
    }

    public function getUri(): UriInterface
    {
        return (new UriFactory())->createUri();
    }

    public function getResourcePath(): string
    {
        return '';
    }

    public function getBodyContents(): ?string
    {
        return null;
    }

    public function getParsedBody(): stdClass
    {
        return (object) [];
    }

    public function getCookieParam(string $name): ?string
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getServerParam(string $name)
    {
        return null;
    }
}
