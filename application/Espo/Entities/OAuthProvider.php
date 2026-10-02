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

use Espo\Core\ORM\Entity;
use stdClass;
use UnexpectedValueException;

class OAuthProvider extends Entity
{
    public const string ENTITY_TYPE = 'OAuthProvider';

    public function isActive(): bool
    {
        return $this->get('isActive');
    }

    public function getClientId(): string
    {
        $value = $this->get('clientId');

        if (!is_string($value)) {
            throw new UnexpectedValueException("No client ID.");
        }

        return $value;
    }

    public function getClientSecret(): string
    {
        $value = $this->get('clientSecret');

        if (!is_string($value)) {
            throw new UnexpectedValueException("No client secret.");
        }

        return $value;
    }

    public function getTokenEndpoint(): string
    {
        $value = $this->get('tokenEndpoint');

        if (!is_string($value)) {
            throw new UnexpectedValueException("No token endpoint.");
        }

        return $value;
    }

    public function getAuthorizationEndpoint(): string
    {
        $value = $this->get('authorizationEndpoint');

        if (!is_string($value)) {
            throw new UnexpectedValueException("No authorization endpoint.");
        }

        return $value;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->get('scopes') ?? [];
    }

    public function getScopeSeparator(): ?string
    {
        return $this->get('scopeSeparator');
    }

    public function getAuthorizationPrompt(): string
    {
        return $this->get('authorizationPrompt');
    }

    public function getAuthorizationParams(): ?stdClass
    {
        return $this->get('authorizationParams') ?? null;
    }
}
