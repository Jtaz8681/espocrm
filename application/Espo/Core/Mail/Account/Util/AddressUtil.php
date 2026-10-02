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

namespace Espo\Core\Mail\Account\Util;

use Espo\Core\Mail\Account\Storage\Params;
use Espo\Core\Mail\SmtpParams;
use Espo\Core\Utils\Config;

/**
 * @internal
 */
class AddressUtil
{
    public function __construct(
        private Config $config,
    ) {}

    /**
     * @internal
     */
    public function isAllowedAddress(Params|SmtpParams $params): bool
    {
        $host = $params instanceof Params ? $params->getHost() : $params->getServer();
        $port = $params->getPort();

        if ($port === null || !$host) {
            return false;
        }

        $address = $host . ':' . $port;

        return in_array($address, $this->getAllowedAddressList());
    }

    /**
     * @return string[]
     */
    private function getAllowedAddressList(): array
    {
        return $this->config->get('emailServerAllowedAddressList') ?? [];
    }
}
