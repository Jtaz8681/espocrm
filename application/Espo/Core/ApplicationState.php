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

namespace Espo\Core;

use Espo\Entities\Portal as PortalEntity;
use Espo\Entities\User as UserEntity;

use LogicException;

/**
 * Provides information about an application, current user, portal.
 */
class ApplicationState
{
    private const KEY_USER = 'user';
    private const KEY_PORTAL = 'portal';

    public function __construct(private Container $container)
    {}

    /**
     * Whether an application is initialized as a portal.
     */
    public function isPortal(): bool
    {
        return $this->container->has(self::KEY_PORTAL);
    }

    /**
     * Get a portal ID (if an application is portal).
     */
    public function getPortalId(): string
    {
        if (!$this->isPortal()) {
            throw new LogicException("Can't get portal ID for non-portal application.");
        }

        return $this->getPortal()->getId();
    }

    /**
     * Get a portal entity (if an application is portal).
     */
    public function getPortal(): PortalEntity
    {
        if (!$this->isPortal()) {
            throw new LogicException("Can't get portal for non-portal application.");
        }

        /** @var PortalEntity */
        return $this->container->get(self::KEY_PORTAL);
    }

    /**
     * Whether any user is initialized. If not logged, it will also return TRUE, meaning the system used is used.
     */
    public function hasUser(): bool
    {
        return $this->container->has(self::KEY_USER);
    }

    /**
     * Get a current logged user. If no auth is applied, then the system user will be returned.
     */
    public function getUser(): UserEntity
    {
        if (!$this->hasUser()) {
            throw new LogicException("User is not yet available.");
        }

        /** @var UserEntity */
        return $this->container->get(self::KEY_USER);
    }

    /**
     * Get an ID of a current logged user. If no auth is applied, then the system user will be returned.
     */
    public function getUserId(): string
    {
        return $this->getUser()->getId();
    }

    /**
     * Whether a user is logged.
     */
    public function isLogged(): bool
    {
        if (!$this->container->has(self::KEY_USER)) {
            return false;
        }

        if ($this->getUser()->isSystem()) {
            return false;
        }

        return true;
    }

    /**
     * Whether logged as an admin.
     */
    public function isAdmin(): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        return $this->getUser()->isAdmin();
    }


    /**
     * Whether logged as an API user.
     */
    public function isApi(): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        return $this->getUser()->isApi();
    }
}
