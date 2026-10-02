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

use Espo\Core\Field\DateTime;
use Espo\Core\Utils\Crypt;
use Espo\Entities\OAuthAccount;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * @internal
 */
class TokenSetter
{
    public function __construct(
        private Crypt $crypt,
    ) {}

    public function set(OAuthAccount $account, AccessTokenInterface $tokens): void
    {
        $accessToken = $this->crypt->encrypt($tokens->getToken());

        $refreshToken = $tokens->getRefreshToken() ?
            $this->crypt->encrypt($tokens->getRefreshToken()) :
            null;

        $expires = $tokens->getExpires() !== null ?
            DateTime::fromTimestamp($tokens->getExpires()) :
            null;

        $account->setAccessToken($accessToken);
        $account->setRefreshToken($refreshToken);
        $account->setExpiresAt($expires);
    }
}
