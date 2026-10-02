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

use Espo\Core\Utils\Json;
use Espo\Core\Utils\Log;
use JsonException;
use RuntimeException;
use SensitiveParameter;

class UserInfoDataProvider
{
    private const REQUEST_TIMEOUT = 10;

    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Log $log,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(#[SensitiveParameter] string $accessToken): array
    {
        return $this->load($accessToken);
    }

    /**
     * @return array<string, mixed>
     */
    private function load(#[SensitiveParameter] string $accessToken): array
    {
        $endpoint = $this->configDataProvider->getUserInfoEndpoint();

        if (!$endpoint) {
            throw new RuntimeException("No userinfo endpoint.");
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ],
        ]);

        /** @var string|false $response */
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $response = '';
        }

        if ($error || is_int($status) && ($status >= 400 && $status < 500)) {
            $this->log->error(self::composeLogMessage('UserInfo response error.', $status, $response));

            throw new RuntimeException("OIDC: Userinfo request error.");
        }

        $parsedResponse = null;

        try {
            $parsedResponse = Json::decode($response, true);
        } catch (JsonException) {}

        if (!is_array($parsedResponse)) {
            throw new RuntimeException("OIDC: Bad userinfo response.");
        }

        return $parsedResponse;
    }

    private static function composeLogMessage(string $text, ?int $status = null, ?string $response = null): string
    {
        if ($status === null) {
            return "OIDC: $text";
        }

        return "OIDC: $text; Status: $status; Response: $response";
    }
}
