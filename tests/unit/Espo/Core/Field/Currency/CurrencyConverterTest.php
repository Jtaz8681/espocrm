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

namespace tests\unit\Espo\Core\Field\Currency;

use Espo\Core\{
    Field\Currency,
    Currency\Converter as CurrencyConverter,
    Currency\ConfigDataProvider as CurrencyConfigDataProvider,
    Currency\Rates as CurrencyRates,
};

class CurrencyConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert1()
    {
        $currencyConfigDataProvider = $this->createMock(CurrencyConfigDataProvider::class);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('hasCurrency')
            ->with('EUR')
            ->willReturn(true);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('getCurrencyRate')
            ->willReturnMap([
                ['USD', 1.0],
                ['EUR', 1.2],
            ]);

        $value = new Currency(2.0, 'USD');

        $converter = new CurrencyConverter($currencyConfigDataProvider);

        $convertedValue = $converter->convert($value, 'EUR');

        $this->assertEquals('EUR', $convertedValue->getCode());

        $this->assertEquals(round(2.0 / 1.2, 10), round($convertedValue->getAmount(), 10));
    }

    public function testConvert2()
    {
        $currencyConfigDataProvider = $this->createMock(CurrencyConfigDataProvider::class);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('hasCurrency')
            ->with('EUR')
            ->willReturn(true);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('getCurrencyRate')
            ->willReturnMap([
                ['USD', 1.0],
                ['EUR', 1.2],
                ['UAH', 0.035],
            ]);

        $value = new Currency(2.0, 'UAH');

        $converter = new CurrencyConverter($currencyConfigDataProvider);

        $convertedValue = $converter->convert($value, 'EUR');

        $this->assertEquals('EUR', $convertedValue->getCode());

        $this->assertEquals(round(2.0 * 0.035 / 1.2, 10), round($convertedValue->getAmount(), 10));
    }

    public function testConvertToDefault()
    {
        $currencyConfigDataProvider = $this->createMock(CurrencyConfigDataProvider::class);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('getDefaultCurrency')
            ->willReturn('USD');

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('hasCurrency')
            ->with('USD')
            ->willReturn(true);

        $currencyConfigDataProvider
            ->expects($this->any())
            ->method('getCurrencyRate')
            ->willReturnMap([
                ['USD', 1.0],
                ['EUR', 1.2],
            ]);

        $value = new Currency(2.0, 'EUR');

        $converter = new CurrencyConverter($currencyConfigDataProvider);

        $convertedValue = $converter->convertToDefault($value);

        $this->assertEquals('USD', $convertedValue->getCode());

        $this->assertEquals(round(2.0 * 1.2, 10), round($convertedValue->getAmount(), 10));
    }

    public function testConvertWithRates()
    {
        $currencyConfigDataProvider = $this->createMock(CurrencyConfigDataProvider::class);

        $rates = CurrencyRates::fromAssoc([
            'USD' => 1.0,
            'EUR' => 1.2,
            'UAH' => 0.035,
        ]);

        $value = new Currency(2.0, 'UAH');

        $converter = new CurrencyConverter($currencyConfigDataProvider);

        $convertedValue = $converter->convertWithRates($value, 'EUR', $rates);

        $this->assertEquals('EUR', $convertedValue->getCode());

        $this->assertEquals(round(2.0 * 0.035 / 1.2, 10), round($convertedValue->getAmount(), 10));
    }
}
