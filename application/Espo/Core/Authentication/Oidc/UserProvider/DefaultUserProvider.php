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

namespace Espo\Core\Authentication\Oidc\UserProvider;

use Espo\Core\ApplicationState;
use Espo\Core\Authentication\Oidc\ConfigDataProvider;
use Espo\Core\Authentication\Oidc\UserProvider;
use Espo\Core\Utils\Log;
use Espo\Entities\User;

use RuntimeException;

class DefaultUserProvider implements UserProvider
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Sync $sync,
        private UserRepository $userRepository,
        private ApplicationState $applicationState,
        private Log $log,
    ) {}

    public function get(UserInfo $userInfo): ?User
    {
        $user = $this->findUser($userInfo);

        if ($user === false) {
            return null;
        }

        if ($user) {
            $this->syncUser($user, $userInfo);

            return $user;
        }

        return $this->tryToCreateUser($userInfo);
    }

    /**
     * @return User|false|null
     */
    private function findUser(UserInfo $userInfo): User|bool|null
    {
        $usernameClaim = $this->configDataProvider->getUsernameClaim();

        if (!$usernameClaim) {
            throw new RuntimeException("No username claim in config.");
        }

        $username = $userInfo->get($usernameClaim);

        if (!$username) {
            throw new RuntimeException("No username claim `$usernameClaim` in token and userinfo.");
        }

        $username = $this->sync->normalizeUsername($username);

        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return null;
        }

        $userId = $user->getId();

        if (!$user->isActive()) {
            $this->log->info("Oidc: User $userId found but it's not active.");

            return false;
        }

        $isPortal = $this->applicationState->isPortal();

        if (!$isPortal && !$user->isRegular() && !$user->isAdmin()) {
            $this->log->info("Oidc: User $userId found but it's neither regular user nor admin.");

            return false;
        }

        if ($isPortal && !$user->isPortal()) {
            $this->log->info("Oidc: User $userId found but it's not portal user.");

            return false;
        }

        if ($isPortal) {
            $portalId = $this->applicationState->getPortalId();

            if (!$user->getPortals()->hasId($portalId)) {
                $this->log->info("Oidc: User $userId found but it's not related to current portal.");

                return false;
            }
        }

        if ($user->isSuperAdmin()) {
            $this->log->info("Oidc: User $userId found but it's super-admin, not allowed.");

            return false;
        }

        if ($user->isAdmin() && !$this->configDataProvider->allowAdminUser()) {
            $this->log->info("Oidc: User $userId found but it's admin, not allowed.");

            return false;
        }

        return $user;
    }

    private function tryToCreateUser(UserInfo $userInfo): ?User
    {
        if (!$this->configDataProvider->createUser()) {
            return null;
        }

        $usernameClaim = $this->configDataProvider->getUsernameClaim();

        if (!$usernameClaim) {
            throw new RuntimeException("Could not create a user. No OIDC username claim in config.");
        }

        $username = $userInfo->get($usernameClaim);

        if (!$username) {
            throw new RuntimeException("Could not create a user. No username claim in token and userinfo.");
        }

        return $this->sync->createUser($userInfo);
    }

    private function syncUser(User $user, UserInfo $userInfo): void
    {
        if (
            !$this->configDataProvider->sync() &&
            !$this->configDataProvider->syncTeams()
        ) {
            return;
        }

        $this->sync->syncUser($user, $userInfo);
    }
}
