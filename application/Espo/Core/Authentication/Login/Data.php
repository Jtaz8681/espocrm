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

namespace Espo\Core\Authentication\Login;

use Espo\Core\Authentication\AuthToken\AuthToken;
use SensitiveParameter;

/**
 * Login data to be passed to the 'login' method.
 */
class Data
{
    private ?string $username;
    private ?string $password;
    private ?AuthToken $authToken;

    public function __construct(
        ?string $username,
        #[SensitiveParameter] ?string $password,
        ?AuthToken $authToken = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->authToken = $authToken;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getAuthToken(): ?AuthToken
    {
        return $this->authToken;
    }

    public function hasUsername(): bool
    {
        return !is_null($this->username);
    }

    public function hasPassword(): bool
    {
        return !is_null($this->password);
    }

    public function hasAuthToken(): bool
    {
        return !is_null($this->authToken);
    }

    public static function createBuilder(): DataBuilder
    {
        return new DataBuilder();
    }
}
