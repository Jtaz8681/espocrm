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

namespace tests\unit\Espo\Core\Select\Where;

use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\Item\Data\DateTime;

use InvalidArgumentException;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
    }

    public function testFromArray()
    {
        $item = Item::createBuilder()
            ->setType('equals')
            ->setAttribute('test')
            ->setValue('testValue')
            ->build();

        $this->assertEquals('equals', $item->getType());
        $this->assertEquals('test', $item->getAttribute());
        $this->assertEquals('testValue', $item->getValue());
        $this->assertNull($item->getData());

        $item1 = Item::createBuilder()
            ->setType('equals')
            ->setAttribute('test')
            ->setValue(1)
            ->build();

        $this->assertEquals('equals', $item1->getType());
        $this->assertEquals('test', $item1->getAttribute());
        $this->assertEquals(1, $item1->getValue());

        $item2 = Item::createBuilder()
            ->setType('equals')
            ->setAttribute('test')
            ->setValue('testValue')
            ->setData(
                DateTime::create()->withTimeZone('Europe/London')
            )
            ->build();

        $this->assertNotNull($item2->getData());
        $this->assertEquals('Europe/London', $item2->getData()->getTimeZone());
    }

    public function testEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        $item = Item::fromRaw([]);
    }

    public function testEmptyAttribute1()
    {
        $this->expectException(InvalidArgumentException::class);

        $item = Item::fromRaw([
            'type' => 'equals',
        ]);
    }

    public function testEmptyAttribute2()
    {
        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [],
        ]);

        $this->assertNotNull($item);
    }

    public function testEmptyType()
    {
        $this->expectException(InvalidArgumentException::class);

        $item = Item::fromRaw([
            'attribute' => 'test',
        ]);
    }

    public function testNonExistingParam()
    {
        $this->expectException(InvalidArgumentException::class);

        $params = Item::fromRaw([
            'bad' => 'd',
        ]);
    }

    public function testGetRaw1()
    {
        $raw = [
            'type' => 'and',
            'value' => [],
        ];

        $item = Item::fromRaw($raw);

        $result = $item->getRaw();

        $this->assertEquals($raw, $result);
    }

    public function testGetRaw2()
    {
        $raw = [
            'type' => 'euqls',
            'attribute' => 'test',
            'value' => '2020-12-12',
            'dateTime' => true,
            'timeZone' => 'UTC',
        ];

        $item = Item::fromRaw($raw);

        $result = $item->getRaw();

        $this->assertEquals($raw, $result);
    }

    public function testGetItemList1()
    {
        $raw = [
            'type' => 'and',
            'value' => [
                [
                    'type' => 'or',
                    'value' => [],
                ],
                [
                    'type' => 'or',
                    'value' => [],
                ],
            ],
        ];

        $item = Item::fromRaw($raw);

        $this->assertEquals('or', $item->getItemList()[0]->getType());
    }

    public function testGetItemList2()
    {
        $item = Item::createBuilder()
            ->setType('and')
            ->setItemList([
                Item::createBuilder()
                    ->setType('or')
                    ->setItemList([])
                    ->build(),
                Item::createBuilder()
                    ->setType('or')
                    ->setItemList([])
                    ->build(),
            ])
            ->build();

        $this->assertEquals('or', $item->getItemList()[0]->getType());
    }
}
