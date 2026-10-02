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

namespace Espo\Classes\RecordHooks\User;

use Espo\Core\Authentication\Logins\Hmac;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Util;
use Espo\Entities\User as UserEntity;
use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\Tools\User\UserUtil;

/**
 * @implements SaveHook<User>
 * @noinspection PhpUnused
 */
class BeforeUpdate implements SaveHook
{
    public function __construct(
        private Config $config,
        private User $user,
        private UserUtil $util
    ) {}

    public function process(Entity $entity): void
    {
        $this->processLimitChecking($entity);
        $this->processUserExistsChecking($entity);
        $this->processApi($entity);
        $this->processTypeChecking($entity);
    }

    /**
     * @throws Conflict
     */
    private function processUserExistsChecking(User $entity): void
    {
        if (!$entity->isAttributeChanged('userName')) {
            return;
        }

        if ($this->util->checkExists($entity)) {
            throw new Conflict('userNameExists');
        }
    }

    /**
     * @throws Forbidden
     */
    private function processLimitChecking(User $entity): void
    {
        $userLimit = $this->config->get('userLimit');
        $portalUserLimit = $this->config->get('portalUserLimit');

        if (
            $userLimit &&
            !$this->user->isSuperAdmin() &&
            (
                (
                    $entity->isActive() &&
                    $entity->isAttributeChanged('isActive') &&
                    !$entity->isPortal() &&
                    !$entity->isApi()
                ) ||
                (
                    !$entity->isPortal() &&
                    !$entity->isApi() &&
                    $entity->isAttributeChanged('type') &&
                    (
                        $entity->isRegular() ||
                        $entity->isAdmin()
                    ) &&
                    (
                        $entity->getFetched('type') == UserEntity::TYPE_PORTAL ||
                        $entity->getFetched('type') == UserEntity::TYPE_API
                    )
                )
            )
        ) {
            $userCount = $this->util->getInternalCount();

            if ($userCount >= $userLimit) {
                throw new Forbidden("User limit $userLimit is reached.");
            }
        }

        if (
            $portalUserLimit &&
            !$this->user->isSuperAdmin() &&
            (
                (
                    $entity->isActive() &&
                    $entity->isAttributeChanged('isActive') &&
                    $entity->isPortal()
                ) ||
                (
                    $entity->isPortal() &&
                    $entity->isAttributeChanged('type')
                )
            )
        ) {
            $portalUserCount = $this->util->getPortalCount();

            if ($portalUserCount >= $portalUserLimit) {
                throw new Forbidden("Portal user limit $portalUserLimit is reached.");
            }
        }
    }

    private function processApi(User $entity): void
    {
        if (
            !$entity->isApi() ||
            !$entity->isAttributeChanged('authMethod') ||
            $entity->getAuthMethod() !== Hmac::NAME
        ) {
            return;
        }

        $secretKey = Util::generateSecretKey();

        $entity->set('secretKey', $secretKey);
    }

    /**
     * @throws Forbidden
     */
    private function processTypeChecking(User $entity): void
    {
        if (
            !$entity->isAttributeChanged(User::ATTR_TYPE) ||
            in_array($entity->getType(), $this->util->getAllowedUserTypeList())
        ) {
            return;
        }

        throw new Forbidden("Can't change type.");
    }
}
