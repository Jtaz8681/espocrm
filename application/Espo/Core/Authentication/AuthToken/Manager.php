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

namespace Espo\Core\Authentication\AuthToken;

/**
 * Fetches and stores auth tokens.
 */
interface Manager
{
    /**
     * Get an auth token. If it does not exist, then returns NULL.
     */
    public function get(string $token): ?AuthToken;

    /**
     * Create an auth token and store it.
     */
    public function create(Data $data): AuthToken;

    /**
     * Make an auth token inactive (invalid).
     */
    public function inactivate(AuthToken $authToken): void;

    /**
     * Update a last access date. An implementation can be omitted to avoid a writing operation.
     */
    public function renew(AuthToken $authToken): void;
}
