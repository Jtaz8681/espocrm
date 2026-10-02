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

namespace Espo\Core\Utils\Currency;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Entities\Currency;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\Currency\RateEntryProvider;
use Espo\Tools\Currency\Exceptions\NotEnabled;

/**
 * Populates currency rates into database.
 */
class DatabasePopulator
{
    private const int PRECISION = 6;

    public function __construct(
        private EntityManager $entityManager,
        private ConfigDataProvider $configDataProvider,
        private RateEntryProvider $rateEntryProvider,
    ) {}

    public function process(): void
    {
        $defaultCurrency = $this->configDataProvider->getDefaultCurrency();
        $baseCurrency = $this->configDataProvider->getBaseCurrency();

        $currencyRates = $this->prepareRates($defaultCurrency, $baseCurrency);

        $this->entityManager->getTransactionManager()->start();

        $delete = $this->entityManager->getQueryBuilder()
            ->delete()
            ->from(Currency::ENTITY_TYPE)
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete);

        foreach ($currencyRates as $currencyName => $rate) {
            $this->entityManager->createEntity(Currency::ENTITY_TYPE, [
                Attribute::ID => $currencyName,
                Currency::FIELD_RATE => $rate,
            ]);
        }

        $this->entityManager->getTransactionManager()->commit();
    }

    /**
     * @param array<string, float> $currencyRates
     * @return array<string, float>
     */
    private function exchangeRates(string $baseCurrency, string $defaultCurrency, array $currencyRates): array
    {
        $defaultCurrencyRate = round(1 / $currencyRates[$defaultCurrency], self::PRECISION);

        $exchangedRates = [];
        $exchangedRates[$baseCurrency] = $defaultCurrencyRate;

        unset($currencyRates[$baseCurrency], $currencyRates[$defaultCurrency]);

        foreach ($currencyRates as $currencyName => $rate) {
            $exchangedRates[$currencyName] = round($rate * $defaultCurrencyRate, self::PRECISION);
        }

        return $exchangedRates;
    }

    /**
     * @return array<string, float>
     */
    private function prepareRates(string $defaultCurrency, string $baseCurrency): array
    {
        $currencyRates = [];

        foreach ($this->configDataProvider->getCurrencyList() as $itCode) {
            try {
                $currencyRates[$itCode] = (float) ($this->rateEntryProvider->getRate($itCode) ?? 1);
            } catch (NotEnabled) {
                continue;
            }
        }

        if ($defaultCurrency !== $baseCurrency) {
            $currencyRates = $this->exchangeRates($baseCurrency, $defaultCurrency, $currencyRates);
        }

        $currencyRates[$defaultCurrency] = 1.00;

        return $currencyRates;
    }
}
