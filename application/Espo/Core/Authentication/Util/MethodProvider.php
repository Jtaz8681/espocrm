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

namespace Espo\Core\Authentication\Util;

use Espo\Core\ApplicationState;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\Core\Authentication\Logins\Espo;
use Espo\Core\ORM\EntityManagerProxy;
use Espo\Core\Utils\Metadata;
use Espo\Entities\AuthenticationProvider;
use Espo\Entities\Portal;
use RuntimeException;

/**
 * An authentication method provider.
 */
class MethodProvider
{
    public function __construct(
        private EntityManagerProxy $entityManager,
        private ApplicationState $applicationState,
        private ConfigDataProvider $configDataProvider,
        private Metadata $metadata
    ) {}

    /**
     * Get an authentication method.
     */
    public function get(): string
    {
        if ($this->applicationState->isPortal()) {
            $method = $this->getForPortal($this->applicationState->getPortal());

            if ($method) {
                return $method;
            }

            return $this->getDefaultForPortal();
        }

        return $this->configDataProvider->getDefaultAuthenticationMethod();
    }

    /**
     * Get an authentication method for portals. The method that is applied via the authentication provider link.
     * If no provider, then returns null.
     */
    public function getForPortal(Portal $portal): ?string
    {
        $providerId = $portal->getAuthenticationProvider()?->getId();

        if (!$providerId) {
            return null;
        }

        /** @var ?AuthenticationProvider $provider */
        $provider = $this->entityManager->getEntityById(AuthenticationProvider::ENTITY_TYPE, $providerId);

        if (!$provider) {
            throw new RuntimeException("No authentication provider for portal.");
        }

        $method = $provider->getMethod();

        if (!$method) {
            throw new RuntimeException("No method in authentication provider.");
        }

        return $method;
    }

    /**
     * Get a default authentication method for portals. Should be used if a portal does not have
     * an authentication provider.
     */
    private function getDefaultForPortal(): string
    {
        $method = $this->configDataProvider->getDefaultAuthenticationMethod();

        $allow = $this->metadata->get(['authenticationMethods', $method, 'portalDefault']);

        if (!$allow) {
            return Espo::NAME;
        }

        return $method;
    }
}
