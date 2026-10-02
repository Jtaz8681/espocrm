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

namespace Espo\Core\Authentication\Jwt\Keys;

use Espo\Core\Authentication\Jwt\Key;
use UnexpectedValueException;
use stdClass;

/**
 * Immutable.
 */
class Rsa implements Key
{
    private string $kid;
    private string $kty;
    private ?string $alg;
    private string $n;
    private string $e;

    private function __construct(stdClass $raw)
    {
        $kid = $raw->kid ?? null;
        $kty = $raw->kty ?? null;
        $alg = $raw->alg ?? null;
        $n = $raw->n ?? null;
        $e = $raw->e ?? null;

        if ($kid === null || $kty === null) {
            throw new UnexpectedValueException("Bad JWK value.");
        }

        if ($n === null || $e === null) {
            throw new UnexpectedValueException("Bad JWK RSE key. No `n` or `e` values.");
        }

        $this->kid = $kid;
        $this->kty = $kty;
        $this->alg = $alg;
        $this->n = $n;
        $this->e = $e;
    }

    public static function fromRaw(stdClass $raw): self
    {
        return new self($raw);
    }

    public function getKid(): string
    {
        return $this->kid;
    }

    public function getKty(): string
    {
        return $this->kty;
    }

    public function getAlg(): ?string
    {
        return $this->alg;
    }

    public function getN(): string
    {
        return $this->n;
    }

    public function getE(): string
    {
        return $this->e;
    }
}
