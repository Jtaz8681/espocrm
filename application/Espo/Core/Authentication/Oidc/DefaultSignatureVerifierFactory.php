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

use Espo\Core\Authentication\Jwt\SignatureVerifier;
use Espo\Core\Authentication\Jwt\SignatureVerifierFactory;
use Espo\Core\Authentication\Jwt\SignatureVerifiers\Hmac;
use Espo\Core\Authentication\Jwt\SignatureVerifiers\Rsa;
use RuntimeException;

class DefaultSignatureVerifierFactory implements SignatureVerifierFactory
{
    private const RS256 = 'RS256';
    private const RS384 = 'RS384';
    private const RS512 = 'RS512';
    private const HS256 = 'HS256';
    private const HS384 = 'HS384';
    private const HS512 = 'HS512';

    private const ALGORITHM_VERIFIER_CLASS_NAME_MAP = [
        self::RS256 => Rsa::class,
        self::RS384 => Rsa::class,
        self::RS512 => Rsa::class,
        self::HS256 => Hmac::class,
        self::HS384 => Hmac::class,
        self::HS512 => Hmac::class,
    ];

    public function __construct(
        private KeysProvider $keysProvider,
        private ConfigDataProvider $configDataProvider
    ) {}

    public function create(string $algorithm): SignatureVerifier
    {
        /** @var ?class-string<SignatureVerifier> $className */
        $className = self::ALGORITHM_VERIFIER_CLASS_NAME_MAP[$algorithm] ?? null;

        if (!$className) {
            throw new RuntimeException("Not supported algorithm $algorithm.");
        }

        if ($className === Rsa::class) {
            $keys = $this->keysProvider->get();

            return new Rsa($algorithm, $keys);
        }

        if ($className === Hmac::class) {
            $key = $this->configDataProvider->getClientSecret();

            if (!$key) {
                throw new RuntimeException("No client secret.");
            }

            return new Hmac($algorithm, $key);
        }

        throw new RuntimeException();
    }
}
