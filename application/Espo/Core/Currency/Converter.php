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

use Espo\Core\Field\Currency;

use RuntimeException;

/**
 * Converts currency values.
 */
class Converter
{
    private ConfigDataProvider $configDataProvider;

    public function __construct(ConfigDataProvider $configDataProvider)
    {
        $this->configDataProvider = $configDataProvider;
    }

    /**
     * Convert a currency value to a specific currency.
     *
     * @throws RuntimeException
     */
    public function convert(Currency $value, string $targetCurrencyCode): Currency
    {
        if (!$this->configDataProvider->hasCurrency($targetCurrencyCode)) {
            throw new RuntimeException("Can't convert currency to unknown currency '{$targetCurrencyCode}.");
        }

        $rate = $this->configDataProvider->getCurrencyRate($value->getCode());
        $targetRate = $this->configDataProvider->getCurrencyRate($targetCurrencyCode);

        $convertedAmount = $this->convertAmount($value->getAmountAsString(), $rate, $targetRate);

        return new Currency($convertedAmount, $targetCurrencyCode);
    }

    /**
     * Convert a currency value to a specific currency with specific rates.
     * Base currency should have rate equal to `1.0`.
     *
     * @throws RuntimeException
     */
    public function convertWithRates(Currency $value, string $targetCurrencyCode, Rates $rates): Currency
    {
        $currencyCode = $value->getCode();

        if (!$rates->hasRate($currencyCode)) {
            throw new RuntimeException("No rate for the currency '{$currencyCode}.");
        }

        if (!$rates->hasRate($targetCurrencyCode)) {
            throw new RuntimeException("No rate for the currency '{$targetCurrencyCode}.");
        }

        $rate = $rates->getRate($currencyCode);
        $targetRate = $rates->getRate($targetCurrencyCode);

        $convertedAmount = $this->convertAmount($value->getAmountAsString(), $rate, $targetRate);

        return new Currency($convertedAmount, $targetCurrencyCode);
    }

    /**
     * Convert a currency value to the system default currency.
     */
    public function convertToDefault(Currency $value): Currency
    {
        $targetCurrencyCode = $this->configDataProvider->getDefaultCurrency();

        return $this->convert($value, $targetCurrencyCode);
    }

    /**
     * @param numeric-string $amount
     * @return numeric-string
     */
    private function convertAmount(string $amount, float $rate, float $targetRate): string
    {
        return CalculatorUtil::divide(
            CalculatorUtil::multiply($amount, (string) $rate),
            (string) $targetRate
        );
    }
}
