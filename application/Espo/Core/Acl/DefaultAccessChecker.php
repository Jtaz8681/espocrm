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

namespace Espo\Core\Acl;

use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessChecker\ScopeChecker;
use Espo\Core\Acl\AccessChecker\ScopeCheckerData;
use Espo\Core\AclManager;
use Espo\Core\Utils\Config;

use DateTime;
use Exception;

/**
 * A default implementation for access checking.
 *
 * @implements AccessEntityCreateChecker<Entity>
 * @implements AccessEntityReadChecker<Entity>
 * @implements AccessEntityEditChecker<Entity>
 * @implements AccessEntityDeleteChecker<Entity>
 * @implements AccessEntityStreamChecker<Entity>
 */
class DefaultAccessChecker implements

    AccessEntityCreateChecker,
    AccessEntityReadChecker,
    AccessEntityEditChecker,
    AccessEntityDeleteChecker,
    AccessEntityStreamChecker
{
    private const ATTR_CREATED_BY_ID = Field::CREATED_BY . 'Id';
    private const ATTR_CREATED_AT = Field::CREATED_AT;
    private const ATTR_ASSIGNED_USER_ID = Field::ASSIGNED_USER . 'Id';
    private const ALLOW_DELETE_OWN_CREATED_PERIOD = '24 hours';

    public function __construct(
        private AclManager $aclManager,
        private Config $config,
        private ScopeChecker $scopeChecker
    ) {}

    /**
     * @param Table::ACTION_* $action
     * @noinspection PhpDocSignatureInspection
     */
    private function checkEntity(User $user, Entity $entity, ScopeData $data, string $action): bool
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwnChecker(
                fn(): bool => $this->aclManager->checkOwnershipOwn($user, $entity)
            )
            ->setInTeamChecker(
                fn(): bool => $this->aclManager->checkOwnershipTeam($user, $entity)
            )
            ->setIsSharedChecker(
                fn(): bool => $this->aclManager->checkOwnershipShared($user, $entity, $action)
            )
            ->build();

        return $this->scopeChecker->check($data, $action, $checkerData);
    }

    private function checkScope(ScopeData $data, ?string $action = null): bool
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->setIsShared(true)
            ->build();

        return $this->scopeChecker->check($data, $action, $checkerData);
    }

    public function check(User $user, ScopeData $data): bool
    {
        return $this->checkScope($data);
    }

    public function checkCreate(User $user, ScopeData $data): bool
    {
        return $this->checkScope($data, Table::ACTION_CREATE);
    }

    public function checkRead(User $user, ScopeData $data): bool
    {
        return $this->checkScope($data, Table::ACTION_READ);
    }

    public function checkEdit(User $user, ScopeData $data): bool
    {
        return $this->checkScope($data, Table::ACTION_EDIT);
    }

    public function checkDelete(User $user, ScopeData $data): bool
    {
        if ($this->checkScope($data, Table::ACTION_DELETE)) {
            return true;
        }

        if ($data->getCreate() === Table::LEVEL_NO) {
            return false;
        }

        if ($this->config->get('aclAllowDeleteCreated')) {
            return true;
        }

        return false;
    }

    public function checkStream(User $user, ScopeData $data): bool
    {
        return $this->checkScope($data, Table::ACTION_STREAM);
    }

    public function checkEntityCreate(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkEntity($user, $entity, $data, Table::ACTION_CREATE);
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkEntity($user, $entity, $data, Table::ACTION_READ);
    }

    public function checkEntityEdit(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkEntity($user, $entity, $data, Table::ACTION_EDIT);
    }

    public function checkEntityStream(User $user, Entity $entity, ScopeData $data): bool
    {
        return $this->checkEntity($user, $entity, $data, Table::ACTION_STREAM);
    }

    public function checkEntityDelete(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($this->checkEntity($user, $entity, $data, Table::ACTION_DELETE)) {
            return true;
        }

        if ($data->getCreate() === Table::LEVEL_NO) {
            return false;
        }

        if (
            !$this->config->get('aclAllowDeleteCreated') ||
            !$entity->has(self::ATTR_CREATED_BY_ID) ||
            $entity->get(self::ATTR_CREATED_BY_ID) !== $user->getId()
        ) {
            return false;
        }

        $isDeletedAllowed = false;

        if (!$entity->has(self::ATTR_ASSIGNED_USER_ID)) {
            $isDeletedAllowed = true;
        } else {
            if (!$entity->get(self::ATTR_ASSIGNED_USER_ID)) {
                $isDeletedAllowed = true;
            } else if ($entity->get(self::ATTR_ASSIGNED_USER_ID) === $entity->get(self::ATTR_CREATED_BY_ID)) {
                $isDeletedAllowed = true;
            }
        }

        if (!$isDeletedAllowed) {
            return false;
        }

        $createdAt = $entity->get(self::ATTR_CREATED_AT);

        if (!$createdAt) {
            return true;
        }

        $deleteThresholdPeriod =
            $this->config->get('aclAllowDeleteCreatedThresholdPeriod') ??
            self::ALLOW_DELETE_OWN_CREATED_PERIOD;

        if (self::isDateTimeAfterPeriod($createdAt, $deleteThresholdPeriod)) {
            return false;
        }

        return true;
    }

    private static function isDateTimeAfterPeriod(string $value, string $period): bool
    {
        try {
            $dt = new DateTime($value);
        } catch (Exception) {
            return false;
        }

        $dt->modify($period);

        $dtNow = new DateTime();

        if ($dtNow->format('U') > $dt->format('U')) {
            return true;
        }

        return false;
    }
}
