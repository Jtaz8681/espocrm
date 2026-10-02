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

namespace Espo\Core\Portal\Utils;

class Url
{
    public static function detectPortalIdForApi(): ?string
    {
        $portalId = filter_input(INPUT_GET, 'portalId');

        if ($portalId)  {
            return $portalId;
       }

        $url = $_SERVER['REQUEST_URI'] ?? null;
        $scriptName = $_SERVER['SCRIPT_NAME'];

        if (!$url) {
            return null;
        }

        $scriptNameModified = str_replace('public/api/', 'api/', $scriptName);

        return explode('/', $url)[count(explode('/', $scriptNameModified)) - 1] ?? null;
    }

    public static function getPortalIdFromEnv(): ?string
    {
        return $_SERVER['ESPO_PORTAL_ID'] ?? null;
    }

    public static function detectPortalId(): ?string
    {
        $portalId = self::getPortalIdFromEnv();

        if ($portalId) {
            return $portalId;
        }

        $url = $_SERVER['REQUEST_URI'] ?? null;
        $scriptName = $_SERVER['SCRIPT_NAME'];

        $scriptNameModified = str_replace('public/api/', 'api/', $scriptName);

        $idIndex = count(explode('/', $scriptNameModified)) - 1;

        if ($url) {
            $portalId = explode('/', $url)[$idIndex] ?? null;

            if (str_contains($url, '=')) {
                $portalId = null;
            }
        }

        if ($portalId) {
            return $portalId;
        }

        $url = $_SERVER['REDIRECT_URL'] ?? null;

        if (!$url) {
            return null;
        }

        $portalId = explode('/', $url)[$idIndex] ?? null;

        if ($portalId === '') {
            $portalId = null;
        }

        return $portalId;
    }

    protected static function detectIsCustomUrl(): bool
    {
        return (bool) ($_SERVER['ESPO_PORTAL_IS_CUSTOM_URL'] ?? false);
    }

    public static function detectIsInPortalDir(): bool
    {
        $isCustomUrl = self::detectIsCustomUrl();

        if ($isCustomUrl) {
            return false;
        }

        $a = explode('?', $_SERVER['REQUEST_URI']);

        $url = rtrim($a[0], '/');

        return str_contains($url, '/portal');
    }

    public static function detectIsInPortalWithId(): bool
    {
        if (!self::detectIsInPortalDir()) {
            return false;
        }

        $url = $_SERVER['REQUEST_URI'];

        $a = explode('?', $url);

        $url = rtrim($a[0], '/');

        $folders = explode('/', $url);

        if (count($folders) > 1 && $folders[count($folders) - 2] === 'portal') {
            return true;
        }

        return false;
    }

    public static function getRedirectUrlWithTrailingSlash(): ?string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = explode('?', $uri, 2)[0];

        if ($path === '' || $path === '/' || str_ends_with($path, '/')) {
            return null;
        }

        $output = $path . '/';

        $queryString = $_SERVER['QUERY_STRING'] ?? null;

        if ($queryString !== null && $queryString !== '') {
            $output .= '?' . $queryString;
        }

        return $output;
    }
}
