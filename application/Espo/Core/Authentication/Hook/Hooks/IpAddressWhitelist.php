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

namespace Espo\Core\Authentication\Hook\Hooks;

use Espo\Core\Api\Request;
use Espo\Core\Api\Util;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\Core\Authentication\Hook\OnLogin;
use Espo\Core\Authentication\Result;
use Espo\Core\Authentication\Util\IpAddressUtil;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config;

class IpAddressWhitelist implements OnLogin
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Util $util,
        private Config $config,
        private IpAddressUtil $ipAddressUtil
    ) {}

    public function process(Result $result, AuthenticationData $data, Request $request): void
    {
        if (!$this->configDataProvider->ipAddressCheck()) {
            return;
        }

        $ipAddress = $this->util->obtainIpFromRequest($request);

        if (
            $ipAddress &&
            $this->ipAddressUtil->isInWhitelist($ipAddress, $this->configDataProvider->getIpAddressWhitelist())
        ) {
            return;
        }

        $user = $result->getUser();

        if ($user && $user->isPortal()) {
            return;
        }

        if ($user && $user->isSuperAdmin() && $this->config->get('restrictedMode')) {
            return;
        }

        if (
            $user &&
            in_array($user->getId(), $this->configDataProvider->getIpAddressCheckExcludedUserIdList())
        ) {
            return;
        }

        $username = $user ? $user->getUserName() : '?';

        throw new Forbidden("Not allowed IP address $ipAddress, user: $username.");
    }
}
