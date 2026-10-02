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

use Psr\Http\Message\RequestInterface as Request;

interface Helper
{
    public function isCredentialsAllowed(Request $request): bool;

    public function getAllowedOrigin(Request $request): ?string;

    /**
     * @return string[]
     */
    public function getAllowedMethods(Request $request): array;

    /**
     * @return string[]
     */
    public function getAllowedHeaders(Request $request): array;

    public function getSuccessStatus(): ?int;

    public function getMaxAge(): ?int;
}
