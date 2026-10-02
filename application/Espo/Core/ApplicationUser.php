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

use Espo\Core\Utils\SystemUser;
use Espo\Entities\User;
use Espo\Core\ORM\EntityManagerProxy;

use Espo\ORM\Name\Attribute;
use RuntimeException;

/**
 * Setting a current user for the application.
 */
class ApplicationUser
{
    /** @deprecated As of v7.4. Different IDs may be used. Use Espo\Core\Utils\SystemUser. */
    public const SYSTEM_USER_ID = 'system';

    public function __construct(
        private Container $container,
        private EntityManagerProxy $entityManagerProxy
    ) {}

    /**
     * Set up the system user as a current user. The system user is used when no user is logged in.
     */
    public function setupSystemUser(): void
    {
        $user = $this->entityManagerProxy
            ->getRDBRepository(User::ENTITY_TYPE)
            ->select([
                Attribute::ID,
                'name',
                'userName',
                'type',
                'isActive',
                'firstName',
                'lastName',
                Attribute::DELETED,
            ])
            ->where(['userName' => SystemUser::NAME])
            ->findOne();

        if (!$user) {
            throw new RuntimeException("System user is not found.");
        }

        $user->set('ipAddress', $_SERVER['REMOTE_ADDR'] ?? null);
        $user->set('type', User::TYPE_SYSTEM);

        $this->container->set('user', $user);
    }

    /**
     * Set a current user.
     */
    public function setUser(User $user): void
    {
        $this->container->set('user', $user);
    }
}
