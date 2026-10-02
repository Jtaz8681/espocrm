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

use Espo\Core\Authentication\AuthToken\AuthToken as AuthTokenInterface;
use Espo\Core\Field\DateTime;
use Espo\Core\ORM\Entity as BaseEntity;

class AuthToken extends BaseEntity implements AuthTokenInterface
{
    public const string ENTITY_TYPE = 'AuthToken';

    /** @since 10.0.0 */
    public const string FIELD_PASSWORD_VERSION = 'passwordVersion';

    public function getToken(): string
    {
        return $this->get('token');
    }

    public function getUserId(): string
    {
        return $this->get('userId');
    }

    public function getPortalId(): ?string
    {
        return $this->get('portalId');
    }

    public function getSecret(): ?string
    {
        return $this->get('secret');
    }

    public function isActive(): bool
    {
        return $this->get('isActive');
    }

    public function getPasswordVersion(): ?int
    {
        return $this->get(self::FIELD_PASSWORD_VERSION);
    }

    /**
     * @since 10.0.0
     */
    public function setPasswordVersion(?int $version): self
    {
        $this->set(self::FIELD_PASSWORD_VERSION, $version);

        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->set('isActive', $isActive);

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->set('userId', $userId);

        return $this;
    }

    public function setPortalId(?string $portalId): self
    {
        $this->set('portalId', $portalId);

        return $this;
    }

    public function setToken(string $token): self
    {
        $this->set('token', $token);

        return $this;
    }

    public function setSecret(string $secret): self
    {
        $this->set('secret', $secret);

        return $this;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->set('ipAddress', $ipAddress);

        return $this;
    }

    public function setLastAccessNow(): self
    {
        $this->set('lastAccess', DateTime::createNow()->toString());

        return $this;
    }
}
