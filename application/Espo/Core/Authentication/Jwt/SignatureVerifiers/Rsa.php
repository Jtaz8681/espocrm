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

use Espo\Core\Authentication\Jwt\Key;
use Espo\Core\Authentication\Jwt\Keys\Rsa as RsaKey;
use Espo\Core\Authentication\Jwt\Token;
use Espo\Core\Authentication\Jwt\SignatureVerifier;
use Espo\Core\Authentication\Jwt\Util;
use LogicException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use RuntimeException;

class Rsa implements SignatureVerifier
{
    private const SUPPORTED_ALGORITHM_LIST = [
        self::RS256,
        self::RS384,
        self::RS512,
    ];

    private const ALGORITHM_MAP = [
        self::RS256 => 'SHA256',
        self::RS384 => 'SHA384',
        self::RS512 => 'SHA512',
    ];

    private const RS256 = 'RS256';
    private const RS384 = 'RS384';
    private const RS512 = 'RS512';

    private string $algorithm;
    /** @var Key[] */
    private array $keys;

    /**
     * @param Key[] $keys
     */
    public function __construct(string $algorithm, array $keys)
    {
        $this->algorithm = $algorithm;
        $this->keys = $keys;

        if (!in_array($algorithm, self::SUPPORTED_ALGORITHM_LIST)) {
            throw new RuntimeException("Unsupported algorithm $algorithm.");
        }
    }

    public function verify(Token $token): bool
    {
        $input = $token->getSigningInput();
        $signature = $token->getSignature();
        $kid = $token->getHeader()->getKid();

        $functionAlgorithm = self::ALGORITHM_MAP[$this->algorithm] ?? null;

        if (!$functionAlgorithm) {
            throw new LogicException();
        }

        $key = array_values(
            array_filter($this->keys, fn ($key) => $key->getKid() === $kid)
        )[0] ?? null;

        if (!$key) {
            return false;
        }

        if (!$key instanceof RsaKey) {
            throw new RuntimeException("Wrong key.");
        }

        $publicKey = openssl_pkey_get_public($this->getPemFromKey($key));

        if ($publicKey === false) {
            throw new RuntimeException("Bad RSA public key.");
        }

        $result = openssl_verify($input, $signature, $publicKey, $functionAlgorithm);

        if ($result === false) {
            throw new RuntimeException("RSA public key verify error: " . openssl_error_string());
        }

        return $result === 1;
    }

    private function getPemFromKey(RsaKey $key): string
    {
        $publicKey = PublicKeyLoader::load([
            'n' => new BigInteger('0x' . bin2hex(Util::base64UrlDecode($key->getN())), 16),
            'e' => new BigInteger('0x' . bin2hex(Util::base64UrlDecode($key->getE())), 16),
        ]);

        $pem = $publicKey->toString('PKCS8');

        if (!is_string($pem)) {
            throw new RuntimeException();
        }

        return $pem;
    }
}
