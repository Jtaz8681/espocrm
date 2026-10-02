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

/**
 * Immutable.
 */
class MetadataParams
{
    private string $method;
    private ?string $credentialsHeader;
    private bool $api;

    public function __construct(
        string $method,
        ?string $credentialsHeader = null,
        bool $api = false
    ) {
        $this->method = $method;
        $this->credentialsHeader = $credentialsHeader;
        $this->api = $api;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRaw(string $method, array $data): self
    {
        return new self(
            $method,
            $data['credentialsHeader'] ?? null,
            $data['api'] ?? false,
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCredentialsHeader(): ?string
    {
        return $this->credentialsHeader;
    }

    public function isApi(): bool
    {
        return $this->api;
    }
}
