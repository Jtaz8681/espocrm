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

use Espo\Core\Utils\Crypt;
use Espo\Entities\OAuthProvider;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * @internal
 */
class GenericProviderFactory
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Crypt $crypt,
    ) {}

    public function create(OAuthProvider $provider): GenericProvider
    {
        $secret = $this->crypt->decrypt($provider->getClientSecret());

        return new GenericProvider([
            'clientId' => $provider->getClientId(),
            'clientSecret' => $secret,
            'redirectUri'  => $this->configDataProvider->getRedirectUri(),
            'urlAccessToken' => $provider->getTokenEndpoint(),

            'urlAuthorize' => 'dummy',
            'urlResourceOwnerDetails' => 'dummy',
        ]);
    }
}
