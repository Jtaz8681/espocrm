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

namespace Espo\Core\Authentication\Util;

use CIDRmatch\CIDRmatch;

class IpAddressUtil
{
    /**
     * @param string $ipAddress An IP address.
     * @param string[] $whitelist A whitelist. IPs or IP ranges in CIDR notation.
     */
    public function isInWhitelist(string $ipAddress, array $whitelist): bool
    {
        $cidrMatch = new CIDRmatch();

        foreach ($whitelist as $whiteIpAddress) {
            if ($ipAddress === $whiteIpAddress) {
                return true;
            }

            if (
                str_contains($whiteIpAddress, '/') &&
                $cidrMatch->match($ipAddress, $whiteIpAddress)
            ) {
                return true;
            }
        }

        return false;
    }
}
