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

namespace Espo\ORM;

use SensitiveParameter;

/**
 * Immutable.
 */
class DatabaseParams
{
    private ?string $platform = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?string $name = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $charset = null;
    private ?string $sslCa = null;
    private ?string $sslCert = null;
    private ?string $sslKey = null;
    private ?string $sslCaPath = null;
    private ?string $sslCipher = null;
    private bool $sslVerifyDisabled = false;

    public static function create(): self
    {
        return new self();
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function getSslCa(): ?string
    {
        return $this->sslCa;
    }

    public function getSslCert(): ?string
    {
        return $this->sslCert;
    }

    public function getSslCaPath(): ?string
    {
        return $this->sslCaPath;
    }

    public function getSslCipher(): ?string
    {
        return $this->sslCipher;
    }

    public function getSslKey(): ?string
    {
        return $this->sslKey;
    }

    public function isSslVerifyDisabled(): bool
    {
        return $this->sslVerifyDisabled;
    }

    public function withPlatform(?string $platform): self
    {
        $obj = clone $this;
        $obj->platform = $platform;

        return $obj;
    }

    public function withHost(?string $host): self
    {
        $obj = clone $this;
        $obj->host = $host;

        return $obj;
    }

    public function withPort(?int $port): self
    {
        $obj = clone $this;
        $obj->port = $port;

        return $obj;
    }

    public function withName(?string $name): self
    {
        $obj = clone $this;
        $obj->name = $name;

        return $obj;
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

    public function withCharset(?string $charset): self
    {
        $obj = clone $this;
        $obj->charset = $charset;

        return $obj;
    }

    public function withSslCa(?string $sslCa): self
    {
        $obj = clone $this;
        $obj->sslCa = $sslCa;

        return $obj;
    }

    public function withSslCaPath(?string $sslCaPath): self
    {
        $obj = clone $this;
        $obj->sslCaPath = $sslCaPath;

        return $obj;
    }

    public function withSslCert(?string $sslCert): self
    {
        $obj = clone $this;
        $obj->sslCert = $sslCert;

        return $obj;
    }

    public function withSslCipher(?string $sslCipher): self
    {
        $obj = clone $this;
        $obj->sslCipher = $sslCipher;

        return $obj;
    }

    public function withSslKey(?string $sslKey): self
    {
        $obj = clone $this;
        $obj->sslKey = $sslKey;

        return $obj;
    }

    public function withSslVerifyDisabled(bool $sslVerifyDisabled = true): self
    {
        $obj = clone $this;
        $obj->sslVerifyDisabled = $sslVerifyDisabled;

        return $obj;
    }
}
