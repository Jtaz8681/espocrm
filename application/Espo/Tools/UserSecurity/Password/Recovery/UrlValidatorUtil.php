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

namespace Espo\Tools\UserSecurity\Password\Recovery;

use const FILTER_VALIDATE_URL;
use const PHP_URL_HOST;

/**
 * @internal
 */
class UrlValidatorUtil
{
    public static function validate(string $url, string $siteUrl): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $siteHost = parse_url($siteUrl, PHP_URL_HOST);

        if ($host !== $siteHost) {
            return false;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (!str_starts_with($url, $siteUrl)) {
            return false;
        }

        return true;
    }
}
