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

namespace Espo\Tools\ExternalAccount;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ExternalAccount\ClientManager;
use Espo\Core\ExternalAccount\Clients\OAuth2Abstract;
use Espo\Core\HookManager;
use Espo\Entities\ExternalAccount;
use Espo\Entities\Integration;
use Espo\ORM\EntityManager;
use Exception;
use RuntimeException;

/**
 * @since 10.0.0
 */
class OAuthService
{
    public function __construct(
        private EntityManager $entityManager,
        private HookManager $hookManager,
        private ClientManager $clientManager,
    ) {}

    /**
     * @throws Error
     * @throws NotFound
     */
    public function authorizationCode(string $integration, string $userId, string $code): void
    {
        $entity = $this->getExternalAccountEntity($integration, $userId);

        if (!$entity) {
            throw new NotFound();
        }

        $entity->setIsEnabled(true);

        $this->entityManager->saveEntity($entity);

        $client = $this->getClient($integration, $userId);

        if (!$client instanceof OAuth2Abstract) {
            throw new RuntimeException("Could not load client for $integration.");
        }

        $result = $client->getAccessTokenFromAuthorizationCode($code);

        if (!$result || empty($result['accessToken'])) {
            throw new Error("Could not get access token for $integration.");
        }

        $entity->clear('accessToken');
        $entity->clear('refreshToken');
        $entity->clear('tokenType');
        $entity->clear('expiresAt');

        foreach ($result as $name => $value) {
            $entity->set($name, $value);
        }

        $this->entityManager->saveEntity($entity);

        $this->hookManager->process('ExternalAccount', 'afterConnect', $entity, [
            'integration' => $integration,
            'userId' => $userId,
            'code' => $code,
        ]);
    }

    public function ping(string $integration, string $userId): bool
    {
        try {
            $client = $this->getClient($integration, $userId);

            if (!$client) {
                return false;
            }

            if (!$client instanceof OAuth2Abstract) {
                throw new Exception("Could not load client for $integration.");
            }

            return $client->ping();
        } catch (Exception) {}

        return false;
    }

    /**
     * @throws NotFound
     * @throws Error
     */
    private function getClient(string $integration, string $id): ?object
    {
        $entity = $this->entityManager->getRDBRepositoryByClass(Integration::class)->getById($integration);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$entity->isEnabled()) {
            throw new Error("$integration is disabled.");
        }

        return $this->clientManager->create($integration, $id);
    }

    private function getExternalAccountEntity(string $integration, string $userId): ?ExternalAccount
    {
        $id = $integration . '__' . $userId;

        return $this->entityManager->getRDBRepositoryByClass(ExternalAccount::class)->getById($id);
    }
}
