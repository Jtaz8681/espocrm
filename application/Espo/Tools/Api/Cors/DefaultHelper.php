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

namespace Espo\Tools\Api\Cors;

use Espo\Core\Utils\Config;
use Psr\Http\Message\RequestInterface as Request;

class DefaultHelper implements Helper
{
    public function __construct(private Config $config) {}

    public function isCredentialsAllowed(Request $request): bool
    {
        return true;
    }

    public function getAllowedOrigin(Request $request): ?string
    {
        $origin = $request->getHeaderLine('Origin');

        if (!$origin) {
            return null;
        }

        return in_array($origin, $this->getAllowedOrigins()) ?
            $origin :
            null;
    }

    public function getAllowedMethods(Request $request): array
    {
        return $this->config->get('apiCorsAllowedMethodList') ?? [];
    }

    public function getAllowedHeaders(Request $request): array
    {
        if (!$request->hasHeader('Access-Control-Request-Headers')) {
            return [];
        }

        return $this->config->get('apiCorsAllowedHeaderList') ?? [];
    }

    public function getSuccessStatus(): ?int
    {
        return null;
    }

    public function getMaxAge(): ?int
    {
        return $this->config->get('apiCorsMaxAge');
    }

    /**
     * @return string[]
     */
    private function getAllowedOrigins(): array
    {
        return $this->config->get('apiCorsAllowedOriginList') ?? [];
    }
}
