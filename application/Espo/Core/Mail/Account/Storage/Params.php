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

/**
 * Immutable.
 */
class Params
{
    /** @since 9.3.0 */
    public const string SECURITY_SSL = 'SSL';
    /** @since 9.3.0 */
    public const string SECURITY_START_TLS = 'TLS';

    /** @var ?class-string<object> */
    private ?string $imapHandlerClassName;

    /** @since 9.3.0 */
    public const string AUTH_MECHANISM_PLAIN = 'plain';

    /** @since 9.3.0 */
    public const string AUTH_MECHANISM_XOAUTH = 'xoauth';

    /**
     * @param ?class-string<object> $imapHandlerClassName
     * @param self::AUTH_MECHANISM_* $authMechanism
     */
    public function __construct(
        private ?string $host,
        private ?int $port,
        private ?string $username,
        private ?string $password,
        private ?string $security,
        ?string $imapHandlerClassName,
        private ?string $id,
        private ?string $userId,
        private ?string $emailAddress,
        private string $authMechanism = self::AUTH_MECHANISM_PLAIN,
    ) {
        $this->imapHandlerClassName = $imapHandlerClassName;
    }

    public static function createBuilder(): ParamsBuilder
    {
        return new ParamsBuilder();
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSecurity(): ?string
    {
        return $this->security;
    }

    /**
     * @return ?class-string
     */
    public function getImapHandlerClassName(): ?string
    {
        return $this->imapHandlerClassName;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * @return self::AUTH_MECHANISM_*
     * @since 9.3.0
     */
    public function getAuthMechanism(): string
    {
        return $this->authMechanism;
    }

    /**
     * @param self::AUTH_MECHANISM_* $authMechanism
     * @since 9.3.0
     */
    public function withAuthMechanism(string $authMechanism): self
    {
        $obj = clone $this;
        $obj->authMechanism = $authMechanism;

        return $obj;
    }

    /**
     * @since 9.3.0
     */
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

    /**
     * @param ?class-string $imapHandlerClassName
     */
    public function withImapHandlerClassName(?string $imapHandlerClassName): self
    {
        $obj = clone $this;
        $obj->imapHandlerClassName = $imapHandlerClassName;

        return $obj;
    }
}
