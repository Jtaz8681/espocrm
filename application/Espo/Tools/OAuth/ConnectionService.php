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

namespace Espo\Tools\OAuth;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\OAuthAccount;
use Espo\ORM\EntityManager;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class ConnectionService
{
    public function __construct(
        private EntityManager $entityManager,
        private GenericProviderFactory $genericProviderFactory,
        private TokenSetter $tokenSetter,
    ) {}

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function connect(OAuthAccount $account, string $code): void
    {
        $provider = $account->getProvider();

        if (!$provider->isActive()) {
            throw new Forbidden("Provider is not active.");
        }

        $genericProvider = $this->genericProviderFactory->create($provider);

        try {
            $tokens = $genericProvider->getAccessToken('authorization_code', ['code' => $code]);
        } catch (GuzzleException $e) {
            throw new Error("Token request error.", 500, $e);
        } catch (IdentityProviderException $e) {
            throw new Error("Token request response error.", 500, $e);
        }

        $this->tokenSetter->set($account, $tokens);

        $this->entityManager->saveEntity($account);
    }

    public function disconnect(OAuthAccount $account): void
    {
        $account->setAccessToken(null);
        $account->setRefreshToken(null);
        $account->setExpiresAt(null);

        $this->entityManager->saveEntity($account);
    }
}
