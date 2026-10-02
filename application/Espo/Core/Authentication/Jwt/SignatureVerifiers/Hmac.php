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

namespace Espo\Core\Authentication\Jwt\SignatureVerifiers;

use Espo\Core\Authentication\Jwt\Token;
use Espo\Core\Authentication\Jwt\SignatureVerifier;
use LogicException;
use RuntimeException;

class Hmac implements SignatureVerifier
{
    private const SUPPORTED_ALGORITHM_LIST = [
        self::HS256,
        self::HS384,
        self::HS512,
    ];

    private const ALGORITHM_MAP = [
        self::HS256 => 'SHA256',
        self::HS384 => 'SHA384',
        self::HS512 => 'SHA512',
    ];

    private const HS256 = 'HS256';
    private const HS384 = 'HS384';
    private const HS512 = 'HS512';

    private string $algorithm;
    private string $key;

    public function __construct(
        string $algorithm,
        string $key
    ) {
        $this->algorithm = $algorithm;
        $this->key = $key;

        if (!in_array($algorithm, self::SUPPORTED_ALGORITHM_LIST)) {
            throw new RuntimeException("Unsupported algorithm $algorithm.");
        }
    }

    public function verify(Token $token): bool
    {
        $input = $token->getSigningInput();
        $signature = $token->getSignature();

        $functionAlgorithm = self::ALGORITHM_MAP[$this->algorithm] ?? null;

        if (!$functionAlgorithm) {
            throw new LogicException();
        }

        $hash = hash_hmac($functionAlgorithm, $input, $this->key, true);

        return $hash === $signature;
    }
}
