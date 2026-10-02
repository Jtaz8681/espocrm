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

namespace Espo\Core\Authentication\AuthToken;

use Espo\Core\Name\Field;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Repository\RDBRepository;
use Espo\Entities\AuthToken as AuthTokenEntity;

use RuntimeException;

/**
 * A default auth token manager. Auth tokens are stored in database.
 * Consider creating a custom implementation if you need to store auth tokens
 * in another storage. E.g. a single Redis data store can be utilized with
 * multiple Espo replicas (for scalability purposes).
 * Defined at metadata > app > containerServices > authTokenManager.
 *
 * @noinspection PhpUnused
 */
class EspoManager implements Manager
{
    /** @var RDBRepository<AuthTokenEntity> */
    private RDBRepository $repository;

    private const int TOKEN_RANDOM_LENGTH = 16;

    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRDBRepositoryByClass(AuthTokenEntity::class);
    }

    public function get(string $token): ?AuthToken
    {
        return $this->repository
            ->select([
                Attribute::ID,
                'isActive',
                'token',
                'secret',
                'userId',
                'portalId',
                'hash',
                AuthTokenEntity::FIELD_PASSWORD_VERSION,
                Field::CREATED_AT,
                'lastAccess',
                Field::MODIFIED_AT,
            ])
            ->where(['token' => $token])
            ->findOne();
    }

    public function create(Data $data): AuthToken
    {
        $authToken = $this->repository->getNew();

        $authToken
            ->setUserId($data->getUserId())
            ->setPortalId($data->getPortalId())
            ->setPasswordVersion($data->getPasswordVersion())
            ->setIpAddress($data->getIpAddress())
            ->setToken($this->generateToken())
            ->setLastAccessNow();

        if ($data->toCreateSecret()) {
            $authToken->setSecret($this->generateToken());
        }

        $this->validate($authToken);

        $this->repository->save($authToken);

        return $authToken;
    }

    public function inactivate(AuthToken $authToken): void
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$authToken instanceof AuthTokenEntity) {
            throw new RuntimeException();
        }

        $this->validateNotChanged($authToken);

        $authToken->setIsActive(false);

        $this->repository->save($authToken);
    }

    public function renew(AuthToken $authToken): void
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$authToken instanceof AuthTokenEntity) {
            throw new RuntimeException();
        }

        $this->validateNotChanged($authToken);

        if ($authToken->isNew()) {
            throw new RuntimeException("Can renew only not new auth token.");
        }

        $authToken->setLastAccessNow();

        $this->repository->save($authToken);
    }

    private function validate(AuthToken $authToken): void
    {
        if (!$authToken->getToken()) {
            throw new RuntimeException("Empty token.");
        }

        if (!$authToken->getUserId()) {
            throw new RuntimeException("Empty user ID.");
        }
    }

    private function validateNotChanged(AuthTokenEntity $authToken): void
    {
        if (
            $authToken->isAttributeChanged('token') ||
            $authToken->isAttributeChanged('secret') ||
            $authToken->isAttributeChanged('hash') ||
            $authToken->isAttributeChanged('userId') ||
            $authToken->isAttributeChanged('portalId')
        ) {
            throw new RuntimeException("Auth token was changed.");
        }
    }

    private function generateToken(): string
    {
        $length = self::TOKEN_RANDOM_LENGTH;

        return bin2hex(random_bytes($length));
    }
}
