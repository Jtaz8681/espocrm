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
use Espo\Core\Authentication\Logout as LogoutInterface;
use Espo\Core\Authentication\Logout\Params;
use Espo\Core\Authentication\Logout\Result;

/**
 * @noinspection PhpUnused
 */
class Logout implements LogoutInterface
{
    public function __construct(
        private ConfigDataProvider $configDataProvider
    ) {}

    public function logout(AuthToken $authToken, Params $params): Result
    {
        $url = $this->configDataProvider->getLogoutUrl();
        $clientId = $this->configDataProvider->getClientId() ?? '';
        $siteUrl = $this->configDataProvider->getSiteUrl();

        if ($url) {
            $url = str_replace('{clientId}', urlencode($clientId), $url);
            $url = str_replace('{siteUrl}', urlencode($siteUrl), $url);
        }

        // @todo Check session is set in auth token to bypass fallback logins.

        return Result::create()->withRedirectUrl($url);
    }
}
