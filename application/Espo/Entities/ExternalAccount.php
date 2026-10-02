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

namespace Espo\Entities;

use Espo\Core\Field\DateTime;
use Exception;

class ExternalAccount extends Integration
{
    public const ENTITY_TYPE = 'ExternalAccount';

    public function isEnabled(): bool
    {
        return (bool) $this->get('enabled');
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->set('enabled', $isEnabled);

        return $this;
    }

    public function unsetData(): self
    {
        $this->set(['data' => null]);

        return $this;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->set('isLocked', $isLocked);

        return $this;
    }

    public function isLocked(): bool
    {
        return (bool) $this->get('isLocked');
    }

    public function getRefreshTokenAttempts(): int
    {
        return (int) ($this->get('refreshTokenAttempts') ?? 0);
    }

    public function getAccessToken(): ?string
    {
        return $this->get('accessToken');
    }

    public function getRefreshToken(): ?string
    {
        return $this->get('refreshToken');
    }

    public function getTokenType(): ?string
    {
        return $this->get('tokenType');
    }

    public function getExpiresAt(): ?DateTime
    {
        $raw = $this->get('expiresAt');

        if (!$raw) {
            return null;
        }

        try {
            return DateTime::fromString($raw);
        } catch (Exception) {
            return null;
        }
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->set('accessToken', $accessToken);

        return $this;
    }

    public function setTokenType(?string $tokenType): self
    {
        $this->set('tokenType', $tokenType);

        return $this;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->set('refreshToken', $refreshToken);

        return $this;
    }


    public function setExpiresAt(?string $expiresAt): self
    {
        $this->set('expiresAt', $expiresAt);

        return $this;
    }

    public function setRefreshTokenAttempts(?int $refreshTokenAttempts): self
    {
        $this->set('refreshTokenAttempts', $refreshTokenAttempts);

        return $this;
    }
}
