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

namespace Espo\Core\Mail;

use Espo\Core\Mail\Sender\TransportPreparator;
use RuntimeException;
use SensitiveParameter;

/**
 * SMTP parameters.
 *
 * Immutable.
 */
class SmtpParams
{
    private ?string $fromAddress = null;
    private ?string $fromName = null;
    /** @var ?array<string, mixed> */
    private ?array $connectionOptions = null;
    private bool $auth = false;
    private ?string $authMechanism = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $security = null;
    /** @var ?class-string<TransportPreparator> */
    private ?string $transportPreparatorClassName = null;

    public const AUTH_MECHANISM_LOGIN = 'login';
    public const AUTH_MECHANISM_CRAMMD5 = 'crammd5';
    public const AUTH_MECHANISM_PLAIN = 'plain';
    public const AUTH_MECHANISM_XOAUTH = 'xoauth';

    public const string SECURITY_SSL_TLS = 'SSL';
    public const string SECURITY_START_TLS = 'TLS';

    /** @var string[] */
    private array $paramList = [
        'server',
        'port',
        'fromAddress',
        'fromName',
        'connectionOptions',
        'auth',
        'authMechanism',
        'username',
        'password',
        'security',
        'transportPreparatorClassName',
    ];

    public function __construct(
        private string $server,
        private int $port
    ) {}

    public static function create(string $server, int $port): self
    {
        return new self($server, $port);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [];

        foreach ($this->paramList as $name) {
            if ($this->$name !== null) {
                $params[$name] = $this->$name;
            }
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $server = $params['server'] ?? null;
        $port = $params['port'] ?? null;
        $auth = $params['auth'] ?? false;

        if ($server === null) {
            throw new RuntimeException("Empty server.");
        }

        if ($port === null) {
            throw new RuntimeException("Empty port.");
        }

        $obj = new self($server, $port);

        $obj->auth = $auth;

        foreach ($obj->paramList as $name) {
            if ($obj->$name !== null) {
                continue;
            }

            if (array_key_exists($name, $params)) {
               $obj->$name = $params[$name];
            }
        }

        return $obj;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getFromAddress(): ?string
    {
        return $this->fromAddress;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function getConnectionOptions(): ?array
    {
        return $this->connectionOptions;
    }

    public function useAuth(): bool
    {
        return $this->auth;
    }

    public function getAuthMechanism(): ?string
    {
        return $this->authMechanism;
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

    public function withFromAddress(?string $fromAddress): self
    {
        $obj = clone $this;
        $obj->fromAddress = $fromAddress;

        return $obj;
    }

    public function withFromName(?string $fromName): self
    {
        $obj = clone $this;
        $obj->fromName = $fromName;

        return $obj;
    }

    /**
     * @param ?array<string, mixed> $connectionOptions
     */
    public function withConnectionOptions(?array $connectionOptions): self
    {
        $obj = clone $this;
        $obj->connectionOptions = $connectionOptions;

        return $obj;
    }

    public function withAuth(bool $auth = true): self
    {
        $obj = clone $this;
        $obj->auth = $auth;

        return $obj;
    }

    public function withAuthMechanism(?string $authMechanism): self
    {
        $obj = clone $this;
        $obj->authMechanism = $authMechanism;

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

    public function withSecurity(?string $security): self
    {
        $obj = clone $this;
        $obj->security = $security;

        return $obj;
    }

    /**
     * @param ?class-string<TransportPreparator> $transportPreparatorClassName
     * @since 9.1.0.
     * @noinspection PhpUnused
     */
    public function withTransportPreparatorClassName(?string $transportPreparatorClassName): self
    {
        $obj = clone $this;
        $obj->transportPreparatorClassName = $transportPreparatorClassName;

        return $obj;
    }

    /**
     * @return ?class-string<TransportPreparator>
     * @since 9.1.0.
     */
    public function getTransportPreparatorClassName(): ?string
    {
        return $this->transportPreparatorClassName;
    }
}
