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

namespace Espo\Tools\Stream\RecordService;

use Espo\Core\Acl\Exceptions\NotAvailable;
use Espo\Core\Acl\Exceptions\NotImplemented as AclNotImplemented;
use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;

class Helper
{
    public function __construct(
        private Metadata $metadata,
        private AclManager $aclManager,
        private UserAclManagerProvider $userAclManagerProvider
    ) {}

    /**
     * @return string[]
     */
    public function getOnlyTeamEntityTypeList(User $user): array
    {
        if ($user->isPortal()) {
            return [];
        }

        $list = [];

        $scopes = $this->metadata->get('scopes', []);

        foreach ($scopes as $scope => $item) {
            if ($scope === User::ENTITY_TYPE) {
                continue;
            }

            if (empty($item['entity'])) {
                continue;
            }

            if (empty($item['object'])) {
                continue;
            }

            if (
                $this->aclManager->checkReadOnlyTeam($user, $scope)
            ) {
                $list[] = $scope;
            }
        }

        return $list;
    }

    /**
     * @return string[]
     */
    public function getOnlyOwnEntityTypeList(User $user): array
    {
        if ($user->isPortal()) {
            return [];
        }

        $list = [];

        $scopes = $this->metadata->get('scopes', []);

        foreach ($scopes as $scope => $item) {
            if ($scope === User::ENTITY_TYPE) {
                continue;
            }

            if (empty($item['entity'])) {
                continue;
            }

            if (empty($item['object'])) {
                continue;
            }

            if ($this->aclManager->checkReadOnlyOwn($user, $scope)) {
                $list[] = $scope;
            }
        }

        return $list;
    }

    /**
     * @return string[]
     */
    public function getIgnoreScopeList(User $user, bool $forParent = false): array
    {
        $ignoreScopeList = [];

        $scopes = $this->metadata->get('scopes', []);

        $aclManager = $this->getUserAclManager($user);

        foreach ($scopes as $scope => $item) {
            if (empty($item['entity'])) {
                continue;
            }

            if (empty($item['object'])) {
                continue;
            }

            try {
                $hasAccess =
                    $aclManager &&
                    $aclManager->checkScope($user, $scope, Table::ACTION_READ) &&
                    (!$forParent || $aclManager->checkScope($user, $scope, Table::ACTION_STREAM));
            } catch (AclNotImplemented) {
                $hasAccess = false;
            }

            if (!$hasAccess) {
                $ignoreScopeList[] = $scope;
            }
        }

        return $ignoreScopeList;
    }

    private function getUserAclManager(User $user): ?AclManager
    {
        try {
            return $this->userAclManagerProvider->get($user);
        } catch (NotAvailable) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public function getNotAllEntityTypeList(User $user): array
    {
        if (!$user->isPortal()) {
            return [];
        }

        $aclManager = $this->getUserAclManager($user);

        $list = [];

        $scopes = $this->metadata->get('scopes', []);

        foreach ($scopes as $scope => $item) {
            if ($scope === User::ENTITY_TYPE) {
                continue;
            }

            if (empty($item['entity'])) {
                continue;
            }

            if (empty($item['object'])) {
                continue;
            }

            if (
                !$aclManager ||
                $aclManager->getLevel($user, $scope, Table::ACTION_READ) !== Table::LEVEL_ALL
            ) {
                $list[] = $scope;
            }
        }

        return $list;
    }
}
