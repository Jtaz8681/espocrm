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

namespace Espo\Core\Authentication;

use SensitiveParameter;

/**
 * Immutable.
 */
class AuthenticationData
{
    private bool $byTokenOnly = false;

    public function __construct(
        private ?string $username = null,
        private ?string $password = null,
        private ?string $method = null
    ) {}

    public static function create(): self
    {
        return new self();
    }

    /**
     * A username.
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * A password or auth-token.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * A method.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Authenticate by auth-token only. No username check.
     */
    public function byTokenOnly(): bool
    {
        return $this->byTokenOnly;
    }

    public function withUsername(?string $username): self
    {
        $obj = clone $this;
        $obj->username = $username;

        return $obj;
    }

    public function withPassword(#[SensitiveParameter] ?string $password): self
    {
        $obj = clone $this;
        $obj->password = $password;

        return $obj;
    }

    public function withMethod(?string $method): self
    {
        $obj = clone $this;
        $obj->method = $method;

        return $obj;
    }

    public function withByTokenOnly(bool $byTokenOnly): self
    {
        $obj = clone $this;
        $obj->byTokenOnly = $byTokenOnly;

        return $obj;
    }
}
