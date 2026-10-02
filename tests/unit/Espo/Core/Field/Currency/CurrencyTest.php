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

use Espo\Core\Field\Currency;
use Espo\Core\Field\Currency\CurrencyFactory;

use Espo\ORM\Entity;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class CurrencyTest extends TestCase
{
    public function testValue1()
    {
        $value = Currency::create(2.0, 'USD');

        $this->assertEquals(2.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testValue()
    {
        $value = Currency::create(0.5, 'USD');

        $this->assertEquals('0.5', $value->getAmountAsString());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testAdd()
    {
        $value = (new Currency(2.0, 'USD'))->add(
            new Currency(1.0, 'USD')
        );

        $this->assertEquals(3.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testSubtract()
    {
        $value = (new Currency(2.0, 'USD'))->subtract(
            new Currency(3.0, 'USD')
        );

        $this->assertEquals(-1.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testMultiply1()
    {
        $value = (new Currency(2.0, 'USD'))->multiply(3.0);

        $this->assertEquals(6.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testMultiply2()
    {
        $value = (new Currency(2.0, 'USD'))->multiply(0.5);

        $this->assertEquals('1.00000000000000', $value->getAmountAsString());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testDivide1()
    {
        $value = (new Currency(6.0, 'USD'))->divide(3.0);

        $this->assertEquals(2.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testDivide2()
    {
        $value = (new Currency(6.0, 'USD'))->divide(0.5);

        $this->assertEquals('12.00000000000000', $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testRound1()
    {
        $value = (new Currency(2.306, 'USD'))->round(2);

        $this->assertEquals(2.31, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testRound2()
    {
        $value = (new Currency(2.306, 'USD'))->round(4);

        $this->assertEquals(2.306, $value->getAmount());
    }

    public function testRound3()
    {
        $value = (new Currency(2.306, 'USD'))->round(0);

        $this->assertEquals(2, $value->getAmount());
    }

    public function testRound4()
    {
        $value = (new Currency(-2.306, 'USD'))->round(2);

        $this->assertEquals(-2.31, $value->getAmount());
    }

    public function testRound5()
    {
        $value = (new Currency(-2.5, 'USD'))->round(0);

        $this->assertEquals(-3, $value->getAmount());
    }

    public function testBadAdd()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Currency(2.0, 'USD'))->add(
            new Currency(1.0, 'EUR')
        );
    }

    public function testGetBadCode()
    {
        $this->expectException(InvalidArgumentException::class);

        new Currency(2.0, '');
    }

    public function testCreateFromEntity()
    {
        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['test', 10.0],
                ['testCurrency', 'USD'],
            ]);

        $entity
            ->expects($this->any())
            ->method('has')
            ->willReturnMap([
                ['test', true],
                ['testCurrency', true],
            ]);

        $factory = new CurrencyFactory();

        $value = $factory->createFromEntity($entity, 'test');

        $this->assertEquals(10.0, $value->getAmount());
        $this->assertEquals('USD', $value->getCode());
    }

    public function testCreatableFromEntityTrue()
    {
        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['test', 5],
                ['testCurrency', 'USD'],
            ]);

        $factory = new CurrencyFactory();

        $this->assertTrue(
            $factory->isCreatableFromEntity($entity, 'test')
        );
    }

    public function testCreatableFromEntityFalse()
    {
        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['test', 5.0],
                ['testCurrency', null],
            ]);

        $factory = new CurrencyFactory();

        $this->assertFalse(
            $factory->isCreatableFromEntity($entity, 'test')
        );
    }

    public function testCompare1(): void
    {
        $this->assertEquals(
            1,
            Currency::create(2.0, 'USD')
                ->compare(
                    Currency::create(1.0, 'USD')
                )
        );
    }

    public function testCompare2(): void
    {
        $this->assertEquals(
            0,
            Currency::create(2.1, 'USD')
                ->compare(
                    Currency::create(2.1, 'USD')
                )
        );
    }

    public function testCompare3(): void
    {
        $this->assertEquals(
            -1,
            Currency::create(2.1, 'USD')
                ->compare(
                    Currency::create(3.1, 'USD')
                )
        );
    }

    public function testCompare4(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Currency::create(2.1, 'EUR')
            ->compare(
                Currency::create(2.1, 'USD')
            );
    }

    public function testIsNegative1(): void
    {
        $this->assertTrue(Currency::create(-1.0, 'USD')->isNegative());
    }

    public function testIsNegative2(): void
    {
        $this->assertFalse(Currency::create(1.0, 'USD')->isNegative());
    }

    public function testIsNegative3(): void
    {
        $this->assertFalse(Currency::create(0.0, 'USD')->isNegative());
    }
}
