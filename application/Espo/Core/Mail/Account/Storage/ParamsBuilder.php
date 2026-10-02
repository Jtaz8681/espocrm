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

namespace Espo\Core\Mail\Account\Storage;

use SensitiveParameter;

class ParamsBuilder
{
    private ?string $host = null;
    private ?int $port = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $security = null;
    /** @var ?class-string<object> */
    private ?string $imapHandlerClassName = null;
    private ?string $id = null;
    private ?string $userId = null;
    private ?string $emailAddress = null;

    public function build(): Params
    {
        return new Params(
            $this->host,
            $this->port,
            $this->username,
            $this->password,
            $this->security,
            $this->imapHandlerClassName,
            $this->id,
            $this->userId,
            $this->emailAddress
        );
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(#[SensitiveParameter] ?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setSecurity(?string $security): self
    {
        $this->security = $security;

        return $this;
    }

    /**
     * @param ?class-string<object> $imapHandlerClassName
     */
    public function setImapHandlerClassName(?string $imapHandlerClassName): self
    {
        $this->imapHandlerClassName = $imapHandlerClassName;

        return $this;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }
}
