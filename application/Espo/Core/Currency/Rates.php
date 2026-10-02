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

namespace Espo\Core\Currency;

use RuntimeException;

/**
 * Currency rates.
 */
class Rates
{
    /** @var array<string, float> */
    private array $data = [];

    private function __construct(private ?string $baseCode = null)
    {}

    /**
     * Create an instance.
     *
     * @param ?string $baseCode A base-currency code.
     */
    public static function create(?string $baseCode = null): self
    {
        return new self($baseCode);
    }

    /**
     * Get a base-currency code.
     *
     * @throws RuntimeException If the base code is not set.
     */
    public function getBase(): string
    {
        if ($this->baseCode === null) {
            throw new RuntimeException("Base code is not set.");
        }

        return $this->baseCode;
    }

    /**
     * Clone with a rate value for a specific currency.
     */
    public function withRate(string $code, float $value): self
    {
        $obj = clone $this;
        $obj->data[$code] = $value;

        return $obj;
    }

    /**
     * Whether a rate is set for a specific currency.
     */
    public function hasRate(string $code): bool
    {
        return array_key_exists($code, $this->data);
    }

    /**
     * Get a rate value for a specific currency.
     */
    public function getRate(string $code): float
    {
        if (!$this->hasRate($code)) {
            throw new RuntimeException("No currency rate for '{$code}'.");
        }

        return $this->data[$code];
    }

    /**
     * To an associative array.
     *
     * @return array<string, float>
     */
    public function toAssoc(): array
    {
        return array_merge(
            $this->data,
            [$this->getBase() => 1.0]
        );
    }

    /**
     * Create from an associative array.
     *
     * @param array<string, float> $data
     */
    public static function fromAssoc(array $data, ?string $baseCode = null): self
    {
        $obj = new self($baseCode);
        $obj->data = $data;

        return $obj;
    }

    /**
     * @deprecated Use `fromAssoc`.
     * @param array<string, float> $data
     */
    public function fromArray(array $data, ?string $baseCode = null): self
    {
        return self::fromAssoc($data, $baseCode);
    }
}
