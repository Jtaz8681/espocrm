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

namespace Espo\Core\ORM\QueryComposer\Part\FunctionConverters;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\QueryComposer\Part\FunctionConverter;
use Espo\ORM\QueryComposer\Util;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class CurrencyRate implements FunctionConverter
{
    private const int PRECISION = 5;

    public function __construct(
        private ConfigDataProvider $config,
    ) {}

    public function convert(string ...$argumentList): string
    {
        $arg = $argumentList[0] ?? null;

        if (!is_string($arg) || !Util::isArgumentString($arg)) {
            throw new RuntimeException("CURRENCY_RATE function accepts only literal string argument.");
        }

        $code = substr($arg, 1, -1);

        if (!in_array($code, $this->config->getCurrencyList())) {
            return Expression::value(0)->getValue();
        }

        $baseCurrency = $this->config->getBaseCurrency();
        $defaultCurrency = $this->config->getDefaultCurrency();

        $rates = $this->config->getCurrencyRates()->toAssoc();

        if ($defaultCurrency !== $baseCurrency) {
            $rates = $this->exchangeRates($baseCurrency, $defaultCurrency, $rates);
        }

        $rate = $rates[$code] ?? 1.0;

        return Expression::value($rate)->getValue();
    }

    /**
     * @param array<string, float> $currencyRates
     * @return array<string, float>
     */
    private function exchangeRates(string $baseCurrency, string $defaultCurrency, array $currencyRates): array
    {
        // Not supposed to happen. If the default currency is not listed in the currency list.
        $defaultNormalRate = $currencyRates[$defaultCurrency] ?? 1.0;

        $defaultCurrencyRate = round(1 / $defaultNormalRate, self::PRECISION);

        $exchangedRates = [];
        $exchangedRates[$baseCurrency] = $defaultCurrencyRate;

        unset($currencyRates[$baseCurrency], $currencyRates[$defaultCurrency]);

        foreach ($currencyRates as $code => $rate) {
            $exchangedRates[$code] = round($rate * $defaultCurrencyRate, self::PRECISION);
        }

        return $exchangedRates;
    }
}
