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

namespace Espo\Tools\AdminNotifications;

class LatestReleaseDataRequester
{
    /**
     * @param array<string, mixed> $requestData
     * @return ?array<int|string, mixed>
     */
    public function request(
        ?string $url = null,
        array $requestData = [],
        string $urlPath = 'release/latest'
    ): ?array {

        if (!function_exists('curl_version')) {
            return null;
        }

        $ch = curl_init();

        $requestUrl = $url ? trim($url) : base64_decode('aHR0cHM6Ly9zLmVzcG9jcm0uY29tLw==');
        $requestUrl = str_ends_with($requestUrl, '/') ? $requestUrl : $requestUrl . '/';

        $requestUrl .= empty($requestData) ?
            $urlPath . '/' :
            $urlPath . '/?' . http_build_query($requestData);

        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);

        /** @var string|false $result */
        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false) {
            return null;
        }

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode($result, true);

        if (!is_array($data)) {
            return null;
        }

        return $data;
    }
}
