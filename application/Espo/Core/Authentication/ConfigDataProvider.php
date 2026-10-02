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

namespace Espo\Core\Authentication;

use Espo\Core\Authentication\Login\MetadataParams;
use Espo\Core\Authentication\Logins\Espo;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;

class ConfigDataProvider
{
    private const string FAILED_ATTEMPTS_PERIOD =  '60 seconds';
    private const string FAILED_CODE_ATTEMPTS_PERIOD =  '5 minutes';
    private const int MAX_FAILED_ATTEMPT_NUMBER = 10;
    private const int MAX_USERNAME_FAILED_ATTEMPT_NUMBER = 30;
    private const string USERNAME_FAILED_ATTEMPTS_PERIOD =  '60 seconds';
    private const int USERNAME_FAILED_ATTEMPT_DELAY = 2;

    public function __construct(private Config $config, private Metadata $metadata)
    {}

    /**
     * A period for max failed attempts checking.
     */
    public function getFailedAttemptsPeriod(): string
    {
        return $this->config->get('authFailedAttemptsPeriod', self::FAILED_ATTEMPTS_PERIOD);
    }

    /**
     * A period for max failed 2FA code attempts checking.
     */
    public function getFailedCodeAttemptsPeriod(): string
    {
        return $this->config->get('authFailedCodeAttemptsPeriod', self::FAILED_CODE_ATTEMPTS_PERIOD);
    }

    /**
     * Max failed log in attempts.
     */
    public function getMaxFailedAttemptNumber(): int
    {
        return $this->config->get('authMaxFailedAttemptNumber', self::MAX_FAILED_ATTEMPT_NUMBER);
    }

    /**
     * Max failed log in attempts for a specific username regardless of the IP address.
     */
    public function getMaxUsernameFailedAttemptNumber(): int
    {
        return $this->config->get('authMaxUsernameFailedAttemptNumber', self::MAX_USERNAME_FAILED_ATTEMPT_NUMBER);
    }

    public function isUsernameFailedAttemptsLimitEnabled(): bool
    {
        return (bool) $this->config->get('authUsernameFailedAttemptsLimitEnabled');
    }

    public function getUsernameFailedAttemptsPeriod(): string
    {
        return $this->config->get('authUsernameFailedAttemptsPeriod', self::USERNAME_FAILED_ATTEMPTS_PERIOD);
    }

    public function getUsernameFailedAttemptsDelay(): int
    {
        return $this->config->get('authUsernameFailedAttemptsDelay', self::USERNAME_FAILED_ATTEMPT_DELAY);
    }

    /**
     * Auth token secret won't be created. Can be reasonable for a custom AuthTokenManager implementation.
     */
    public function isAuthTokenSecretDisabled(): bool
    {
        return (bool) $this->config->get('authTokenSecretDisabled');
    }

    /**
     * A maintenance mode. Only admin can log in.
     */
    public function isMaintenanceMode(): bool
    {
        return (bool) $this->config->get('maintenanceMode');
    }

    /**
     * Whether 2FA is enabled.
     */
    public function isTwoFactorEnabled(): bool
    {
        return (bool) $this->config->get('auth2FA');
    }

    /**
     * Whether 2FA is enabled in portals.
     *
     * @since 10.0.4
     */
    public function isTwoFactorInPortalEnabled(): bool
    {
        return (bool) $this->config->get('auth2FAInPortal');
    }

    /**
     * Allowed methods of 2FA.
     *
     * @return array<int, string>
     */
    public function getTwoFactorMethodList(): array
    {
        return $this->config->get('auth2FAMethodList') ?? [];
    }

    /**
     * A user won't be able to have multiple active auth tokens simultaneously.
     */
    public function preventConcurrentAuthToken(): bool
    {
        return (bool) $this->config->get('authTokenPreventConcurrent');
    }

    /**
     * A default authentication method.
     */
    public function getDefaultAuthenticationMethod(): string
    {
        return $this->config->get('authenticationMethod', Espo::NAME);
    }

    /**
     * Whether an authentication method can be defined by request itself (in a header).
     */
    public function authenticationMethodIsApi(string $authenticationMethod): bool
    {
        return (bool) $this->metadata->get(['authenticationMethods', $authenticationMethod, 'api']);
    }

    public function isAnotherUserDisabled(): bool
    {
        return (bool) $this->config->get('authAnotherUserDisabled');
    }

    public function isAuthLogDisabled(): bool
    {
        return (bool) $this->config->get('authLogDisabled');
    }

    public function isApiUserAuthLogDisabled(): bool
    {
        return (bool) $this->config->get('authApiUserLogDisabled');
    }

    /**
     * @return MetadataParams[]
     */
    public function getLoginMetadataParamsList(): array
    {
        $list = [];

        /** @var array<string, array<string, mixed>> $data */
        $data = $this->metadata->get(['authenticationMethods']) ?? [];

        foreach ($data as $method => $item) {
            $list[] = MetadataParams::fromRaw($method, $item);
        }

        return $list;
    }

    public function ipAddressCheck(): bool
    {
        return (bool) $this->config->get('authIpAddressCheck');
    }

    /**
     * @return string[]
     */
    public function getIpAddressWhitelist(): array
    {
        return $this->config->get('authIpAddressWhitelist') ?? [];
    }

    /**
     * @return string[]
     */
    public function getIpAddressCheckExcludedUserIdList(): array
    {
        return $this->config->get('authIpAddressCheckExcludedUsersIds') ?? [];
    }
}
