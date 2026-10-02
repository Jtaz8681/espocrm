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

namespace Espo\Hooks\Common;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;

class CurrencyConverted
{
    public static int $order = 1;

    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Metadata $metadata,
    ) {}

    public function beforeSave(Entity $entity): void
    {
        $fieldDefs = $this->metadata->get(['entityDefs', $entity->getEntityType(), 'fields'], []);

        foreach ($fieldDefs as $fieldName => $defs) {
            if (empty($defs['type']) || $defs['type'] !== FieldType::CURRENCY_CONVERTED) {
                continue;
            }

            $currencyFieldName = substr($fieldName, 0, -9);

            $currencyCurrencyFieldName = $currencyFieldName . 'Currency';

            if (
                !$entity->isAttributeChanged($currencyFieldName) &&
                !$entity->isAttributeChanged($currencyCurrencyFieldName)
            ) {
                continue;
            }

            if (empty($fieldDefs[$currencyFieldName])) {
                continue;
            }

            if ($entity->get($currencyFieldName) === null) {
                $entity->set($fieldName, null);

                continue;
            }

            $currency = $entity->get($currencyCurrencyFieldName);
            $value = $entity->get($currencyFieldName);

            if (!$currency) {
                continue;
            }

            $rates = $this->configDataProvider->getCurrencyRates()->toAssoc();
            $baseCurrency = $this->configDataProvider->getBaseCurrency();
            $defaultCurrency = $this->configDataProvider->getDefaultCurrency();

            $targetValue = $value;

            if ($defaultCurrency !== $currency) {
                $targetValue = $targetValue / ($rates[$baseCurrency] ?? 1.0);
                $targetValue = $targetValue * ($rates[$currency] ?? 1.0);

                $targetValue = round($targetValue, 2);
            }

            $entity->set($fieldName, $targetValue);
        }
    }
}
