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

use RuntimeException;
use SensitiveParameter;

/**
 * An auth token data. Used for auth token creation.
 *
 * Immutable.
 */
class Data
{
    private string $userId;
    private ?string $portalId = null;
    private ?string $ipAddress = null;
    private bool $createSecret = false;
    private ?int $passwordVersion = null;

    private function __construct()
    {}

    /**
     * A user ID.
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * A portal ID.
     */
    public function getPortalId(): ?string
    {
        return $this->portalId;
    }

    /**
     * An ID address.
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * A password version.
     *
     * @since 10.0.0
     */
    public function getPasswordVersion(): ?int
    {
        return $this->passwordVersion;
    }

    /**
     * To create a secret.
     */
    public function toCreateSecret(): bool
    {
        return $this->createSecret;
    }

    /**
     * @param array{
     *     userId: string,
     *     portalId?: ?string,
     *     passwordVersion?: ?int,
     *     ipAddress?: ?string,
     *     createSecret?: ?bool,
     * } $data
     */
    public static function create(#[SensitiveParameter] array $data): self
    {
        $obj = new self();

        $userId = $data['userId'] ?? null;

        if (!$userId) {
            throw new RuntimeException("No user ID.");
        }

        $obj->userId = $userId;
        $obj->portalId = $data['portalId'] ?? null;
        $obj->ipAddress = $data['ipAddress'] ?? null;
        $obj->createSecret = $data['createSecret'] ?? false;
        $obj->passwordVersion = $data['passwordVersion'] ?? null;

        return $obj;
    }
}
