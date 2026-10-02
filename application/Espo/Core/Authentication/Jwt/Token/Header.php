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

namespace Espo\Core\Authentication\Jwt\Token;

use Espo\Core\Utils\Json;
use RuntimeException;
use JsonException;
use stdClass;

/**
 * Immutable.
 */
class Header
{
    private string $alg;
    private ?string $kid;
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        string $alg,
        ?string $kid,
        array $data
    ) {
        $this->alg = $alg;
        $this->kid = $kid;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    public static function fromRaw(string $raw): self
    {
        $parsed = null;

        try {
            $parsed = Json::decode($raw);
        } catch (JsonException) {}

        if (!$parsed instanceof stdClass) {
            throw new RuntimeException();
        }

        $alg = self::obtainFromParsedString($parsed, 'alg');
        $kid = self::obtainFromParsedStringNull($parsed, 'kid');

        return new self(
            $alg,
            $kid,
            get_object_vars($parsed)
        );
    }

    /** @noinspection PhpSameParameterValueInspection */
    private static function obtainFromParsedString(stdClass $parsed, string $name): string
    {
        $value = $parsed->$name ?? null;

        if (!is_string($value)) {
            throw new RuntimeException("No or bad `$name` in JWT header.");
        }

        return $value;
    }

    /** @noinspection PhpSameParameterValueInspection */
    private static function obtainFromParsedStringNull(stdClass $parsed, string $name): ?string
    {
        $value = $parsed->$name ?? null;

        if ($value !== null && !is_string($value)) {
            throw new RuntimeException("Bad `$name` in JWT header.");
        }

        return $value;
    }

    public function getAlg(): string
    {
        return $this->alg;
    }

    public function getKid(): ?string
    {
        return $this->kid;
    }
}
