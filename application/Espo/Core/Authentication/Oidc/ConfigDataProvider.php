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

namespace Espo\Core\Authentication\Oidc;

use Espo\Core\ApplicationState;
use Espo\Core\ORM\EntityManagerProxy;
use Espo\Core\Utils\Config;
use Espo\Entities\AuthenticationProvider;
use stdClass;

class ConfigDataProvider
{
    private const JWKS_CACHE_PERIOD = '10 minutes';

    private Config|AuthenticationProvider $object;

    public function __construct(
        private Config $config,
        private ApplicationState $applicationState,
        private EntityManagerProxy $entityManager
    ) {
        $this->object = $this->getAuthenticationProvider() ?? $this->config;
    }

    private function isAuthenticationProvider(): bool
    {
        return $this->object instanceof AuthenticationProvider;
    }

    private function getAuthenticationProvider(): ?AuthenticationProvider
    {
        if (!$this->applicationState->isPortal()) {
            return null;
        }

        $link = $this->applicationState->getPortal()->getAuthenticationProvider();

        if (!$link) {
            return null;
        }

        /** @var ?AuthenticationProvider */
        return $this->entityManager->getEntityById(AuthenticationProvider::ENTITY_TYPE, $link->getId());
    }

    public function getSiteUrl(): string
    {
        $siteUrl = $this->isAuthenticationProvider() ?
            $this->applicationState->getPortal()->getUrl() :
            $this->config->get('siteUrl');

        return rtrim($siteUrl, '/');
    }

    public function getRedirectUri(): string
    {
        return $this->getSiteUrl() . '/oauth-callback.php';
    }

    public function getClientId(): ?string
    {
        return $this->object->get('oidcClientId');
    }

    public function getClientSecret(): ?string
    {
        return $this->object->get('oidcClientSecret');
    }

    public function getAuthorizationEndpoint(): ?string
    {
        return $this->object->get('oidcAuthorizationEndpoint');
    }

    public function getTokenEndpoint(): ?string
    {
        return $this->object->get('oidcTokenEndpoint');
    }

    public function getUserInfoEndpoint(): ?string
    {
        return $this->object->get('oidcUserInfoEndpoint');
    }

    public function getJwksEndpoint(): ?string
    {
        return $this->object->get('oidcJwksEndpoint');
    }

    /**
     * @return string[]
     */
    public function getJwtSignatureAlgorithmList(): array
    {
        return $this->object->get('oidcJwtSignatureAlgorithmList') ?? [];
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        /** @var string[] */
        return $this->object->get('oidcScopes') ?? [];
    }

    public function getLogoutUrl(): ?string
    {
        return $this->object->get('oidcLogoutUrl');
    }

    public function getUsernameClaim(): ?string
    {
        return $this->object->get('oidcUsernameClaim');
    }

    public function createUser(): bool
    {
        return (bool) $this->object->get('oidcCreateUser');
    }

    public function sync(): bool
    {
        return (bool) $this->object->get('oidcSync');
    }

    public function syncTeams(): bool
    {
        if ($this->isAuthenticationProvider()) {
            return false;
        }

        return (bool) $this->config->get('oidcSyncTeams');
    }

    public function fallback(): bool
    {
        if ($this->isAuthenticationProvider()) {
            return false;
        }

        return (bool) $this->config->get('oidcFallback');
    }

    public function allowRegularUserFallback(): bool
    {
        if ($this->isAuthenticationProvider()) {
            return false;
        }

        return (bool) $this->config->get('oidcAllowRegularUserFallback');
    }

    public function allowAdminUser(): bool
    {
        if ($this->isAuthenticationProvider()) {
            return false;
        }

        return (bool) $this->config->get('oidcAllowAdminUser');
    }

    public function getGroupClaim(): ?string
    {
        if ($this->isAuthenticationProvider()) {
            return null;
        }

        return $this->config->get('oidcGroupClaim');
    }

    /**
     * @return ?string[]
     */
    public function getTeamIds(): ?array
    {
        if ($this->isAuthenticationProvider()) {
            return null;
        }

        return $this->config->get('oidcTeamsIds') ?? [];
    }

    public function getTeamColumns(): ?stdClass
    {
        if ($this->isAuthenticationProvider()) {
            return null;
        }

        return $this->config->get('oidcTeamsColumns') ?? (object) [];
    }

    public function getAuthorizationPrompt(): string
    {
        return $this->object->get('oidcAuthorizationPrompt') ?? 'consent';
    }

    public function useAuthorizationPkce(): bool
    {
        return (bool) $this->object->get('oidcAuthorizationPkce');
    }

    public function getAuthorizationMaxAge(): ?int
    {
        return $this->config->get('oidcAuthorizationMaxAge');
    }

    public function getJwksCachePeriod(): string
    {
        return $this->config->get('oidcJwksCachePeriod') ?? self::JWKS_CACHE_PERIOD;
    }
}
