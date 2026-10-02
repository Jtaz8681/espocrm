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

use Espo\Core\Currency\ConfigDataProvider as CurrencyConfigDataProvider;
use Espo\Core\Currency\InternalRatesProvider;
use Espo\Core\Utils\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CurrencyConfigDataProviderTest extends TestCase
{
    private $config;
    private $provider;
    private $ratesProvider;

    protected function setUp() : void
    {
        $this->config = $this->createMock(Config::class);
        $this->ratesProvider = $this->createMock(InternalRatesProvider::class);

        $this->provider = new CurrencyConfigDataProvider(
            $this->config,
            $this->ratesProvider,
        );
    }

    public function testDefaultCurrency()
    {
        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('defaultCurrency')
            ->willReturn('USD');

        $currency = $this->provider->getDefaultCurrency();

        $this->assertEquals('USD', $currency);
    }

    public function testBaseCurrency()
    {
        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('baseCurrency')
            ->willReturn('USD');

        $currency = $this->provider->getBaseCurrency();

        $this->assertEquals('USD', $currency);
    }

    public function testCurrencyList()
    {
        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('currencyList')
            ->willReturn(['USD', 'EUR']);

        $result = $this->provider->getCurrencyList();

        $this->assertEquals(['USD', 'EUR'], $result);
    }

    public function testHasCurrency()
    {
        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('currencyList')
            ->willReturn(['USD', 'EUR']);

        $result = $this->provider->hasCurrency('EUR');

        $this->assertTrue($result);
    }

    public function testCurrencyRate1()
    {
        $this->ratesProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn([
                'EUR' => 1.2,
            ]);

        $invokedCount = $this->exactly(2);

        $this->config
            ->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(function ($param) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('baseCurrency', $param);

                    return 'USD';
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('currencyList', $param);

                    return ['USD', 'EUR'];
                }

                throw new RuntimeException();
            });

        $result = $this->provider->getCurrencyRate('EUR');

        $this->assertEquals(1.2, $result);
    }

    public function testCurrencyRate2()
    {
        $this->ratesProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn([
                'EUR' => 1.2,
            ]);

        $invokedCount = $this->exactly(2);

        $this->config
            ->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(function ($param) use ($invokedCount) {
                if ($invokedCount->numberOfInvocations() === 1) {
                    $this->assertEquals('baseCurrency', $param);

                    return 'USD';
                }

                if ($invokedCount->numberOfInvocations() === 2) {
                    $this->assertEquals('currencyList', $param);

                    return ['USD', 'EUR'];
                }

                throw new RuntimeException();
            });

        $result = $this->provider->getCurrencyRate('USD');

        $this->assertEquals(1.0, $result);
    }
}
