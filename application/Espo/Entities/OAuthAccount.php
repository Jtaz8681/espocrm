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

/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Espo\Entities;

use Espo\Core\Field\DateTime;
use Espo\Core\ORM\Entity;
use SensitiveParameter;
use UnexpectedValueException;

class OAuthAccount extends Entity
{
    public const string ENTITY_TYPE = 'OAuthAccount';

    public function getProvider(): OAuthProvider
    {
        $provider = $this->relations->getOne('provider');

        if (!$provider instanceof OAuthProvider) {
            throw new UnexpectedValueException("No provider.");
        }

        return $provider;
    }

    public function getAccessToken(): ?string
    {
        return $this->get('accessToken');
    }

    public function getRefreshToken(): ?string
    {
        return $this->get('refreshToken');
    }

    public function getExpiresAt(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject('expiresAt');
    }

    public function setAccessToken(#[SensitiveParameter] ?string $accessToken): self
    {
        return $this->set('accessToken', $accessToken);
    }

    public function setRefreshToken(#[SensitiveParameter] ?string $refreshToken): self
    {
        return $this->set('refreshToken', $refreshToken);
    }

    public function setExpiresAt(?DateTime $expiresAt): self
    {
        return $this->setValueObject('expiresAt', $expiresAt);
    }
}
