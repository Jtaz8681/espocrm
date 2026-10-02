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

namespace Espo\Tools\Oidc;

use Espo\Core\Authentication\Jwt\Exceptions\Invalid;
use Espo\Core\Authentication\Oidc\ConfigDataProvider;
use Espo\Core\Authentication\Oidc\Login as OidcLogin;
use Espo\Core\Authentication\Oidc\BackchannelLogout;
use Espo\Core\Authentication\Oidc\PkceUtil;
use Espo\Core\Authentication\Util\MethodProvider;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Session\Session;
use Espo\Core\Utils\Json;

class Service
{
    public function __construct(
        private BackchannelLogout $backchannelLogout,
        private MethodProvider $methodProvider,
        private ConfigDataProvider $configDataProvider,
        private Session $session,
    ) {}

    /**
     * @return array{
     *     clientId: non-empty-string,
     *     endpoint: non-empty-string,
     *     redirectUri: string,
     *     scopes: non-empty-array<string>,
     *     claims: ?string,
     *     prompt: 'none'|'login'|'consent'|'select_account',
     *     maxAge: ?int,
     *     codeChallenge: ?string,
     *     codeChallengeMethod: ?string,
     * }
     * @throws Forbidden
     * @throws Error
     */
    public function getAuthorizationData(): array
    {
        if ($this->methodProvider->get() !== OidcLogin::NAME) {
            throw new Forbidden();
        }

        $clientId = $this->configDataProvider->getClientId();
        $endpoint = $this->configDataProvider->getAuthorizationEndpoint();
        $scopes = $this->configDataProvider->getScopes();
        $groupClaim = $this->configDataProvider->getGroupClaim();
        $redirectUri = $this->configDataProvider->getRedirectUri();
        $codeChallenge = $this->configDataProvider->useAuthorizationPkce() ? $this->prepareCodeChallenge() : null;

        if (!$clientId) {
            throw new Error("No client ID.");
        }

        if (!$endpoint) {
            throw new Error("No authorization endpoint.");
        }

        array_unshift($scopes, 'openid');

        $claims = null;

        if ($groupClaim) {
            $claims = Json::encode([
                'id_token' => [
                    $groupClaim => ['essential' => true],
                ],
            ]);
        }

        /** @var 'none'|'login'|'consent'|'select_account' $prompt
         * @noinspection PhpRedundantVariableDocTypeInspection
         */
        $prompt = $this->configDataProvider->getAuthorizationPrompt();
        $maxAge = $this->configDataProvider->getAuthorizationMaxAge();

        return [
            'clientId' => $clientId,
            'endpoint' => $endpoint,
            'redirectUri' => $redirectUri,
            'scopes' => $scopes,
            'claims' => $claims,
            'prompt' => $prompt,
            'maxAge' => $maxAge,
            'codeChallenge' => $codeChallenge,
            'codeChallengeMethod' => $codeChallenge ? 'S256' : null,
        ];
    }

    /**
     * @throws Forbidden
     */
    public function backchannelLogout(string $rawToken): void
    {
        if ($this->methodProvider->get() !== OidcLogin::NAME) {
            throw new Forbidden();
        }

        try {
            $this->backchannelLogout->logout($rawToken);
        } catch (Invalid $e) {
            throw new Forbidden("OIDC logout: Invalid JWT. " . $e->getMessage());
        }
    }

    private function prepareCodeChallenge(): string
    {
        $codeVerifier = PkceUtil::generateCodeVerifier();

        $this->session->set(OidcLogin::SESSION_KEY_CODE_VERIFIER, $codeVerifier);

        return PkceUtil::hashAndEncodeCodeVerifier($codeVerifier);
    }
}
