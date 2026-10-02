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

namespace Espo\Classes\AppParams;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\Utils\NumberUtil;
use Espo\Tools\App\AppParam;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class CurrencyRates implements AppParam
{
    private const int PRECISION = 6;

    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private NumberUtil $numberUtil,
    ) {}

    public function get(): stdClass
    {
        $rates = $this->configDataProvider->getCurrencyRates()->toAssoc();

        foreach ($rates as $code => $value) {
            $rates[$code] = $this->numberUtil->format($value, self::PRECISION, '.', '');
        }

        return (object) $rates;
    }
}
