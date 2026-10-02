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

use Espo\Core\Acl\Cache\Clearer as AclCacheClearer;
use Espo\Core\ApplicationState;
use Espo\Core\Authentication\Oidc\ConfigDataProvider;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\PasswordHash;
use Espo\Core\Utils\Util;
use Espo\Entities\User;
use RuntimeException;

class Sync
{
    public function __construct(
        private UsernameValidator $usernameValidator,
        private Config $config,
        private ConfigDataProvider $configDataProvider,
        private UserRepository $userRepository,
        private PasswordHash $passwordHash,
        private AclCacheClearer $aclCacheClearer,
        private ApplicationState $applicationState,
        private UserInfoPopulator $userInfoPopulator,
    ) {}

    public function createUser(UserInfo $userInfo): User
    {
        $username = $this->getUsernameFromToken($userInfo);

        $this->usernameValidator->validate($username);

        $user = $this->userRepository->getNew();

        $user->setType(User::TYPE_REGULAR);
        $user->setUserName($username);

        $user->setMultiple([
            'password' => $this->passwordHash->hash(Util::generatePassword(10, 4, 2, true)),
        ]);

        $this->userInfoPopulator->populate($userInfo, $user);
        $user->set($this->getUserTeamsDataFromToken($userInfo));

        if ($this->applicationState->isPortal()) {
            $portalId = $this->applicationState->getPortalId();

            $user->setType(User::TYPE_PORTAL);
            $user->setPortals(LinkMultiple::create()->withAddedId($portalId));
        }

        $this->userRepository->save($user);

        return $user;
    }

    public function syncUser(User $user, UserInfo $payload): void
    {
        $username = $this->getUsernameFromToken($payload);

        $this->usernameValidator->validate($username);

        if ($user->getUserName() !== $username) {
            throw new RuntimeException("Could not sync user. Username mismatch.");
        }

        if ($this->configDataProvider->sync()) {
            $this->userInfoPopulator->populate($payload, $user);
        }

        $clearAclCache = false;

        if ($this->configDataProvider->syncTeams()) {
            $user->loadLinkMultipleField(Field::TEAMS);

            $user->set($this->getUserTeamsDataFromToken($payload));

            $clearAclCache = $user->isAttributeChanged('teamsIds');
        }

        $this->userRepository->save($user);

        if ($clearAclCache) {
            $this->aclCacheClearer->clearForUser($user);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserTeamsDataFromToken(UserInfo $userInfo): array
    {
        return [
            'teamsIds' => $this->getTeamIdList($userInfo),
        ];
    }

    private function getUsernameFromToken(UserInfo $userInfo): string
    {
        $usernameClaim = $this->configDataProvider->getUsernameClaim();

        if (!$usernameClaim) {
            throw new RuntimeException("No OIDC username claim in config.");
        }

        $username = $userInfo->get($usernameClaim);

        if (!$username) {
            throw new RuntimeException("No username claim returned in token.");
        }

        if (!is_string($username)) {
            throw new RuntimeException("Bad username claim returned in token.");
        }

        return $this->normalizeUsername($username);
    }

    /**
     * @return string[]
     */
    private function getTeamIdList(UserInfo $userInfo): array
    {
        $idList = $this->configDataProvider->getTeamIds() ?? [];
        $columns = $this->configDataProvider->getTeamColumns() ?? (object) [];

        if ($idList === []) {
            return [];
        }

        $groupList = $this->getGroups($userInfo);

        $resultIdList = [];

        foreach ($idList as $id) {
            $group = ($columns->$id ?? (object) [])->group ?? null;

            if (!$group || in_array($group, $groupList)) {
                $resultIdList[] = $id;
            }
        }

        return $resultIdList;
    }

    /**
     * @return string[]
     */
    private function getGroups(UserInfo $userInfo): array
    {
        $groupClaim = $this->configDataProvider->getGroupClaim();

        if (!$groupClaim) {
            return [];
        }

        $value = $userInfo->get($groupClaim);

        if (!$value) {
            return [];
        }

        if (is_string($value)) {
            return [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $list = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $list[] = $item;
            }
        }

        return $list;
    }

    public function normalizeUsername(string $username): string
    {
        /** @var ?string $regExp */
        $regExp = $this->config->get('userNameRegularExpression');

        if (!$regExp) {
            throw new RuntimeException("No `userNameRegularExpression` in config.");
        }

        $username = strtolower($username);

        /** @var string $result */
        $result = preg_replace("/$regExp/", '_', $username);

        /** @var string */
        return str_replace(' ', '_', $result);
    }
}
