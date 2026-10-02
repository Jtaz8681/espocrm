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

namespace tests\unit\Espo\Core\Currency;

use Espo\Core\Currency\Rates;

class RatesTest extends \PHPUnit\Framework\TestCase
{
    public function testRates1(): void
    {
        $rates = Rates
            ::create('USD')
            ->withRate('EUR', 1.2);

        $this->assertEquals('USD', $rates->getBase());
        $this->assertEquals(true, $rates->hasRate('EUR'));
        $this->assertEquals(1.2, $rates->getRate('EUR'));

        $this->assertEquals(['EUR' => 1.2, 'USD' => 1.0], $rates->toAssoc());
    }

    public function testRates2(): void
    {
        $rates = Rates::fromAssoc(['EUR' => 1.2], 'USD');

        $this->assertEquals(['EUR' => 1.2, 'USD' => 1.0], $rates->toAssoc());
    }
}
