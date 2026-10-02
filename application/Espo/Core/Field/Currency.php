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

namespace Espo\Core\Field;

use Espo\Core\Currency\CalculatorUtil;

use InvalidArgumentException;

/**
 * A currency value object. Immutable.
 */
class Currency
{
    private const int DEFAULT_SCALE = 14;

    /** @var numeric-string */
    private string $amount;
    private string $code;

    /**
     * @param numeric-string|float|int $amount An amount.
     * @param string $code A currency code.
     * @throws InvalidArgumentException
     */
    public function __construct($amount, string $code)
    {
        if (!is_string($amount) && !is_float($amount) && !is_int($amount)) {
            throw new InvalidArgumentException();
        }

        if (strlen($code) !== 3) {
            throw new InvalidArgumentException("Bad currency code.");
        }

        if (is_int($amount)) {
            $amount = (string) $amount;
        } else if (is_float($amount)) {
            $amount = self::floatToString($amount);
        }

        $this->amount = $amount;
        $this->code = $code;
    }

    /**
     * Get an amount as string.
     *
     * @return numeric-string
     */
    public function getAmountAsString(): string
    {
        return $this->amount;
    }

    /**
     * Get an amount.
     */
    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    /**
     * Get a currency code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Add a currency value.
     *
     * @throws InvalidArgumentException If currency codes are different.
     */
    public function add(self $value): self
    {
        if ($this->getCode() !== $value->getCode()) {
            throw new InvalidArgumentException("Can't add a currency value with a different code.");
        }

        $amount = CalculatorUtil::add(
            $this->getAmountAsString(),
            $value->getAmountAsString()
        );

        return new self($amount, $this->getCode());
    }

    /**
     * Subtract a currency value.
     *
     * @throws InvalidArgumentException If currency codes are different.
     */
    public function subtract(self $value): self
    {
        if ($this->getCode() !== $value->getCode()) {
            throw new InvalidArgumentException("Can't subtract a currency value with a different code.");
        }

        $amount = CalculatorUtil::subtract(
            $this->getAmountAsString(),
            $value->getAmountAsString()
        );

        return new self($amount, $this->getCode());
    }

    /**
     * Multiply by a multiplier.
     *
     * @param float|int|numeric-string $multiplier
     */
    public function multiply(float|int|string $multiplier): self
    {
        $multiplier = is_float($multiplier) ? self::floatToString($multiplier) : (string) $multiplier;

        $amount = CalculatorUtil::multiply($this->getAmountAsString(), $multiplier);

        return new self($amount, $this->getCode());
    }

    /**
     * Divide by a divider.
     *
     * @param float|int|numeric-string $divider
     */
    public function divide(float|int|string $divider): self
    {
        $divider = is_float($divider) ? self::floatToString($divider) : (string) $divider;

        $amount = CalculatorUtil::divide($this->getAmountAsString(), $divider);

        return new self($amount, $this->getCode());
    }

    /**
     * Round with a precision.
     */
    public function round(int $precision = 0): self
    {
        $amount = CalculatorUtil::round($this->getAmountAsString(), $precision);

        return new self($amount, $this->getCode());
    }

    /**
     * Compare with another currency value. Returns:
     * - `1` if greater than the value;
     * - `0` if equal to the value;
     * - `-1` if less than the value.
     *
     * @throws InvalidArgumentException If currency codes are different.
     */
    public function compare(self $value): int
    {
        if ($this->getCode() !== $value->getCode()) {
            throw new InvalidArgumentException("Can't compare currencies with different codes.");
        }

        return CalculatorUtil::compare(
            $this->getAmountAsString(),
            $value->getAmountAsString()
        );
    }

    /**
     * Check whether the value is negative.
     */
    public function isNegative(): bool
    {
        return $this->compare(self::create(0.0, $this->code)) === -1;
    }

    /**
     * Create from an amount and code.
     *
     * @param numeric-string|float|int $amount An amount.
     * @param string $code A currency code.
     * @throws InvalidArgumentException
     */
    public static function create($amount, string $code): self
    {
        return new self($amount, $code);
    }

    /**
     * @return numeric-string
     */
    private static function floatToString(float $amount): string
    {
        /** @var numeric-string */
        return rtrim(rtrim(sprintf('%.' . self::DEFAULT_SCALE . 'f', $amount), '0'), '.');
    }
}
