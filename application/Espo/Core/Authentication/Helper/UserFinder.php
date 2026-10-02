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

namespace Espo\Core\Authentication\Helper;

use Espo\Core\Authentication\Logins\ApiKey;
use Espo\Core\Authentication\Logins\Hmac;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;

/**
 * @internal
 */
class UserFinder
{
    /** @var string[] */
    private const array FORBIDDEN_USER_TYPE_LIST = [
        User::TYPE_API,
        User::TYPE_SYSTEM,
    ];

    public function __construct(private EntityManager $entityManager)
    {}

    public function find(string $username): ?User
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([
                User::FIELD_USER_NAME => $username,
                User::FIELD_TYPE . '!=' => self::FORBIDDEN_USER_TYPE_LIST,
            ])
            ->findOne();
    }

    public function findByAuthTokenData(string $username, string $id, ?int $passwordVersion): ?User
    {
        $where = [
            User::FIELD_USER_NAME => $username,
            Attribute::ID => $id,
            User::FIELD_TYPE . '!=' => self::FORBIDDEN_USER_TYPE_LIST,
        ];

        if ($passwordVersion !== null) {
            $where[User::FIELD_PASSWORD_VERSION] = $passwordVersion;
        }

        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where($where)
            ->findOne();
    }

    public function findApiHmac(string $apiKey): ?User
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([
                User::FIELD_TYPE => User::TYPE_API,
                'apiKey' => $apiKey,
                'authMethod' => Hmac::NAME,
            ])
            ->findOne();
    }

    public function findApiApiKey(string $apiKey): ?User
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([
                User::FIELD_TYPE => User::TYPE_API,
                'apiKey' => $apiKey,
                'authMethod' => ApiKey::NAME,
            ])
            ->findOne();
    }
}
