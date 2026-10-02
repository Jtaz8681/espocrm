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

namespace Espo\Classes\AclPortal\Note;

use Espo\Core\Name\Field;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Portal\Acl\DefaultAccessChecker;
use Espo\Core\Portal\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\Portal\AclManager;
use Espo\Core\Utils\Config;

use DateTime;
use Exception;

/**
 * @implements AccessEntityCREDChecker<Note>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    private const EDIT_PERIOD = '7 days';
    private const DELETE_PERIOD = '1 month';

    private DefaultAccessChecker $defaultAccessChecker;
    private AclManager $aclManager;
    private EntityManager $entityManager;
    private Config $config;

    public function __construct(
        DefaultAccessChecker $defaultAccessChecker,
        AclManager $aclManager,
        EntityManager $entityManager,
        Config $config
    ) {
        $this->defaultAccessChecker = $defaultAccessChecker;
        $this->aclManager = $aclManager;
        $this->entityManager = $entityManager;
        $this->config = $config;
    }

    /**
     * @param Note $entity
     */
    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        $parentId = $entity->getParentId();
        $parentType = $entity->getParentType();

        if (!$parentId || !$parentType) {
            return $this->defaultAccessChecker->checkEntityCreate($user, $entity, $data);
        }

        $parent = $this->entityManager->getEntityById($parentType, $parentId);

        if ($parent && $this->aclManager->checkEntityStream($user, $parent)) {
            return true;
        }

        return $this->defaultAccessChecker->checkEntityCreate($user, $entity, $data);
    }

    /**
     * @param Note $entity
     */
    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        $parentId = $entity->getParentId();
        $parentType = $entity->getParentType();

        if ($entity->isInternal()) {
            return false;
        }

        if ($parentId && $parentType) {
            $parent = $this->entityManager->getEntityById($parentType, $parentId);

            if (!$parent) {
                return false;
            }

            return $this->aclManager->checkEntityStream($user, $parent);
        }

        if ($entity->getType() !== Note::TYPE_POST) {
            return false;
        }

        if ($entity->getCreatedById() === $user->getId()) {
            return true;
        }

        if ($entity->getTargetType() === Note::TARGET_PORTALS) {
            return in_array($user->getPortalId(), $entity->getLinkMultipleIdList('portals'));
        }

        return false;
    }

    /**
     * @param Note $entity
     */
    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$this->defaultAccessChecker->checkEntityEdit($user, $entity, $data)) {
            return false;
        }

        if (!$this->aclManager->checkOwnershipOwn($user, $entity)) {
            return false;
        }

        $createdAt = $entity->get(Field::CREATED_AT);

        if (!$createdAt) {
            return true;
        }

        $noteEditThresholdPeriod =
            '-' .  $this->config->get('noteEditThresholdPeriod', self::EDIT_PERIOD);

        $dt = new DateTime();

        $dt->modify($noteEditThresholdPeriod);

        try {
            if ($dt->format('U') > (new DateTime($createdAt))->format('U')) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param Note $entity
     */
    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if (!$this->defaultAccessChecker->checkEntityDelete($user, $entity, $data)) {
            return false;
        }

        if (!$this->aclManager->checkOwnershipOwn($user, $entity)) {
            return false;
        }

        $createdAt = $entity->get(Field::CREATED_AT);

        if (!$createdAt) {
            return true;
        }

        $deleteThresholdPeriod =
            '-' . $this->config->get('noteDeleteThresholdPeriod', self::DELETE_PERIOD);

        $dt = new DateTime();

        $dt->modify($deleteThresholdPeriod);

        try {
            if ($dt->format('U') > (new DateTime($createdAt))->format('U')) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
