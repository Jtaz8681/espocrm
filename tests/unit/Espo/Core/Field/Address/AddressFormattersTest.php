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

namespace tests\unit\Espo\Core\Field\Address;

use Espo\Core\{
    Field\Address,
};

use Espo\Classes\{
    AddressFormatters\Formatter1,
    AddressFormatters\Formatter2,
    AddressFormatters\Formatter3,
    AddressFormatters\Formatter4,
};

class AddressFormattersTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {

    }

    public function testFormat1All()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $formatter = new Formatter1();

        $expected =
            "street\n" .
            "city, state postalCode\n" .
            "country";

        $result = $formatter->format($address);

        $this->assertEquals($expected, $result);
    }

    public function testFormat1NoState()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState(null)
            ->setPostalCode('postalCode')
            ->build();

        $formatter = new Formatter1();

        $expected =
            "street\n" .
            "city postalCode\n" .
            "country";

        $result = $formatter->format($address);

        $this->assertEquals($expected, $result);
    }

    public function testFormat2All()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $formatter = new Formatter2();

        $expected =
            "street\n" .
            "postalCode city\n" .
            "state country";

        $result = $formatter->format($address);

        $this->assertEquals($expected, $result);
    }

    public function testFormat3All()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $formatter = new Formatter3();

        $expected =
            "country\n" .
            "state postalCode city\n" .
            "street";

        $result = $formatter->format($address);

        $this->assertEquals($expected, $result);
    }

    public function testFormat4All()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $formatter = new Formatter4();

        $expected =
            "street\n" .
            "city\n" .
            "country - state postalCode";

        $result = $formatter->format($address);

        $this->assertEquals($expected, $result);
    }
}
