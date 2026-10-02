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

use Espo\Core\Utils\Config;
use RuntimeException;

class ConfigDataProvider
{
    public function __construct(
        private Config $config,
        private InternalRatesProvider $internalRatesProvider,
    ) {}

    /**
     * Get decimal places.
     *
     * @since 8.3.0
     */
    public function getDecimalPlaces(): ?int
    {
        return $this->config->get('currencyDecimalPlaces');
    }

    /**
     * Get a system default currency.
     */
    public function getDefaultCurrency(): string
    {
        return $this->config->get('defaultCurrency');
    }

    /**
     * Get a base currency, used for conversion.
     */
    public function getBaseCurrency(): string
    {
        return $this->config->get('baseCurrency');
    }

    /**
     * Get a list of available currencies.
     *
     * @return string[]
     */
    public function getCurrencyList(): array
    {
        return $this->config->get('currencyList') ?? [];
    }

    /**
     * Whether a currency is available in the system.
     */
    public function hasCurrency(string $currencyCode): bool
    {
        return in_array($currencyCode, $this->getCurrencyList());
    }

    /**
     * Get a rate of a specific currency related to the base currency.
     */
    public function getCurrencyRate(string $currencyCode): float
    {
        $rates = $this->internalRatesProvider->get($this->getBaseCurrency());

        if (!$this->hasCurrency($currencyCode)) {
            throw new RuntimeException("Can't get currency rate of '$currencyCode' currency.");
        }

        return $rates[$currencyCode] ?? 1.0;
    }

    /**
     * Get rates.
     */
    public function getCurrencyRates(): Rates
    {
        $rates = $this->internalRatesProvider->get($this->getBaseCurrency());

        $rates[$this->getBaseCurrency()] = 1.0;

        return Rates::fromAssoc($rates, $this->getBaseCurrency());
    }
}
