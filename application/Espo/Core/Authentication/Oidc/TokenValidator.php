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

use Espo\Core\Authentication\Jwt\Exceptions\Invalid;
use Espo\Core\Authentication\Jwt\Exceptions\SignatureNotVerified;
use Espo\Core\Authentication\Jwt\SignatureVerifierFactory;
use Espo\Core\Authentication\Jwt\Token;
use RuntimeException;

class TokenValidator
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private SignatureVerifierFactory $signatureVerifierFactory
    ) {}

    /**
     * @throws SignatureNotVerified
     * @throws Invalid
     */
    public function validateSignature(Token $token): void
    {
        $algorithm = $token->getHeader()->getAlg();

        $allowedAlgorithmList = $this->configDataProvider->getJwtSignatureAlgorithmList();

        if (!in_array($algorithm, $allowedAlgorithmList)) {
            throw new Invalid("JWT signing algorithm `$algorithm` not allowed.");
        }

        $verifier = $this->signatureVerifierFactory->create($algorithm);

        if (!$verifier->verify($token)) {
            throw new SignatureNotVerified("JWT signature not verified.");
        }
    }

    /**
     * @throws Invalid
     */
    public function validateFields(Token $token): void
    {
        $oidcClientId = $this->configDataProvider->getClientId();

        if (!$oidcClientId) {
            throw new RuntimeException("OIDC: No client ID.");
        }

        if (!in_array($oidcClientId, $token->getPayload()->getAud())) {
            throw new Invalid("JWT the `aud` field does not contain matching client ID.");
        }

        if (!$token->getPayload()->getSub()) {
            throw new Invalid("JWT does not contain the `sub` value.");
        }

        if (!$token->getPayload()->getIss()) {
            throw new Invalid("JWT does not contain the `iss` value.");
        }
    }
}
