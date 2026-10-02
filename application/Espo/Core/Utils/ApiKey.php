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

namespace Espo\Core\Utils;

use Espo\Core\Utils\Config\ConfigWriter;
use SensitiveParameter;

class ApiKey
{
    public function __construct(
        private Config $config,
        private ConfigWriter $configWriter)
    {}

    public static function hash(string $secretKey, string $string = ''): string
    {
        return hash_hmac('sha256', $string, $secretKey);
    }

    /**
     * @deprecated
     * @internal
     */
    public static function hashLegacy(string $secretKey, string $string = ''): string
    {
        return hash_hmac('sha256', $string, $secretKey, true);
    }

    public function getSecretKeyForUserId(string $id): ?string
    {
        $apiSecretKeys = $this->config->get('apiSecretKeys');

        if (!$apiSecretKeys) {
            return null;
        }

        if (!is_object($apiSecretKeys)) {
            return null;
        }

        if (!isset($apiSecretKeys->$id)) {
            return null;
        }

        return $apiSecretKeys->$id;
    }

    public function storeSecretKeyForUserId(string $id, #[SensitiveParameter] string $secretKey): void
    {
        $apiSecretKeys = $this->config->get('apiSecretKeys');

        if (!is_object($apiSecretKeys)) {
            $apiSecretKeys = (object) [];
        }

        $apiSecretKeys->$id = $secretKey;

        $this->configWriter->set('apiSecretKeys', $apiSecretKeys);
        $this->configWriter->save();
    }

    public function removeSecretKeyForUserId(string $id): void
    {
        $apiSecretKeys = $this->config->get('apiSecretKeys');

        if (!is_object($apiSecretKeys)) {
            $apiSecretKeys = (object) [];
        }

        unset($apiSecretKeys->$id);

        $this->configWriter->set('apiSecretKeys', $apiSecretKeys);
        $this->configWriter->save();
    }
}
