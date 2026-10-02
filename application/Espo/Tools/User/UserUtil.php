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

namespace Espo\Tools\User;

use Espo\Entities\User;
use Espo\Entities\User as UserEntity;
use Espo\ORM\EntityManager;

/**
 * @internal
 */
class UserUtil
{
    /** @var string[] */
    private $allowedUserTypeList = [
        UserEntity::TYPE_REGULAR,
        UserEntity::TYPE_ADMIN,
        UserEntity::TYPE_PORTAL,
        UserEntity::TYPE_API,
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function getInternalCount(): int
    {
        return $this->entityManager
            ->getRDBRepository(User::ENTITY_TYPE)
            ->where([
                'isActive' => true,
                'type' => [
                    User::TYPE_ADMIN,
                    User::TYPE_REGULAR,
                ],
            ])
            ->count();
    }

    public function getPortalCount(): int
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([
                'isActive' => true,
                'type' => User::TYPE_PORTAL,
            ])
            ->count();
    }

    public function checkExists(User $user): bool
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where(['userName' => $user->getUserName()])
            ->findOne() !== null;
    }

    /**
     * @return string[]
     */
    public function getAllowedUserTypeList(): array
    {
        return $this->allowedUserTypeList;
    }
}
