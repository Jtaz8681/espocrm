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

namespace Espo\Core\Formula\Functions\ExtGroup\CurrencyGroup;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\Currency\Converter;
use Espo\Core\Field\Currency;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;

class ConvertType implements Func
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Converter $converter
    ) {}

    public function process(EvaluatedArgumentList $arguments): string
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $amount = $arguments[0];
        $fromCode = $arguments[1];
        $toCode = $arguments[2] ?? $this->configDataProvider->getDefaultCurrency();

        if (
            !is_string($amount) &&
            !is_int($amount) &&
            !is_float($amount)
        ) {
            throw BadArgumentType::create(1, 'int|float|string');
        }

        if (!is_string($fromCode)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($toCode)) {
            throw BadArgumentType::create(3, 'string');
        }

        assert(is_numeric($amount));

        $value = Currency::create($amount, $fromCode);

        $convertedValue = $this->converter->convert($value, $toCode);

        return $convertedValue->getAmountAsString();
    }
}
