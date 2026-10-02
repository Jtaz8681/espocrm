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

namespace Espo\Core\Authentication\Oidc;

use Espo\Core\Authentication\AuthToken\AuthToken;
use Espo\Core\Authentication\AuthToken\Manager as AuthTokenManager;
use Espo\Core\Authentication\Jwt\Exceptions\Invalid;
use Espo\Core\Authentication\Jwt\Exceptions\SignatureNotVerified;
use Espo\Core\Authentication\Jwt\Token;
use Espo\Core\Authentication\Jwt\Validator;
use Espo\Core\Authentication\Oidc\UserProvider\UserRepository;
use Espo\Core\Utils\Log;
use Espo\Entities\AuthToken as AuthTokenEntity;
use Espo\ORM\EntityManager;

/**
 * Compatible only with default Espo auth tokens.
 *
 * @todo Use a token-sessionId map to retrieve tokens. Send sid claim in id_token.
 */
class BackchannelLogout
{
    public function __construct(
        private Log $log,
        private Validator $validator,
        private TokenValidator $tokenValidator,
        private ConfigDataProvider $configDataProvider,
        private UserRepository $userRepository,
        private EntityManager $entityManager,
        private AuthTokenManager $authTokenManger
    ) {}

    /**
     * @throws SignatureNotVerified
     * @throws Invalid
     */
    public function logout(string $rawToken): void
    {
        $token = Token::create($rawToken);

        $this->log->debug("OIDC logout: JWT header: " . $token->getHeaderRaw());
        $this->log->debug("OIDC logout: JWT payload: " . $token->getPayloadRaw());

        $this->validator->validate($token);
        $this->tokenValidator->validateSignature($token);
        $this->tokenValidator->validateFields($token);

        $usernameClaim = $this->configDataProvider->getUsernameClaim();

        if (!$usernameClaim) {
            throw new Invalid("No username claim in config.");
        }

        $username = $token->getPayload()->get($usernameClaim);

        if (!$username) {
            throw new Invalid("No username claim `$usernameClaim` in token.");
        }

        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return;
        }

        $authTokenList = $this->entityManager
            ->getRDBRepositoryByClass(AuthTokenEntity::class)
            ->where([
                'userId' => $user->getId(),
                'isActive' => true,
            ])
            ->find();

        foreach ($authTokenList as $authToken) {
            assert($authToken instanceof AuthToken);

            $this->authTokenManger->inactivate($authToken);
        }
    }
}
