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
    Field\Address\AddressFactory,
};

use Espo\ORM\Entity;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {

    }

    public function testAddress1()
    {
        $address = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $this->assertEquals('street', $address->getStreet());
        $this->assertEquals('city', $address->getCity());
        $this->assertEquals('country', $address->getCountry());
        $this->assertEquals('state', $address->getState());
        $this->assertEquals('postalCode', $address->getPostalCode());
    }

    public function testBuilderClone()
    {
        $addressOriginal = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $address = Address::createBuilder()
            ->clone($addressOriginal)
            ->build();

        $this->assertEquals('street', $address->getStreet());
        $this->assertEquals('city', $address->getCity());
        $this->assertEquals('country', $address->getCountry());
        $this->assertEquals('state', $address->getState());
        $this->assertEquals('postalCode', $address->getPostalCode());
    }

    public function testAddressWith()
    {
        $addressOriginal = Address::createBuilder()
            ->setStreet('street')
            ->setCity('city')
            ->setCountry('country')
            ->setState('state')
            ->setPostalCode('postalCode')
            ->build();

        $address = $addressOriginal->withStreet('new street');

        $this->assertEquals('new street', $address->getStreet());
        $this->assertEquals('city', $address->getCity());
    }

    public function testCreateFromEntity()
    {
        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['addressStreet', 'street'],
                ['addressCity', 'city'],
                ['addressCountry', 'country'],
                ['addressState', null],
                ['addressPostalCode', null],
            ]);

        $factory = new AddressFactory();

        $address = $factory->createFromEntity($entity, 'address');

        $this->assertEquals('street', $address->getStreet());
        $this->assertEquals('city', $address->getCity());
        $this->assertEquals('country', $address->getCountry());
        $this->assertEquals(null, $address->getState());
        $this->assertEquals(null, $address->getPostalCode());
    }

    public function testCreateFromNothing()
    {
        $address = Address::create();

        $this->assertEquals(null, $address->getState());
    }
}
