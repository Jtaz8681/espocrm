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

namespace tests\unit\Espo\Core\Field\LinkMultiple;

use Espo\Core\Field\LinkMultiple;
use Espo\Core\Field\LinkMultipleItem;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class LinkMultipleTest extends TestCase
{
    public function testEmpty()
    {
        $value = LinkMultiple::create();

        $this->assertEquals(0, count($value->getIdList()));
        $this->assertEquals(0, count($value->getList()));
        $this->assertEquals(0, $value->getCount());
    }

    public function testDuplicate()
    {
        $this->expectException(InvalidArgumentException::class);

        LinkMultiple
            ::create([
                LinkMultipleItem::create('1'),
                LinkMultipleItem::create('1'),
            ]);
    }

    public function testWithAdded1()
    {
        $item = LinkMultipleItem::create('1')->withName('test-1');

        $group = LinkMultiple
            ::create([$item])
            ->withAdded(
                LinkMultipleItem::create('2')
            );

        $this->assertEquals(2, $group->getCount());

        $this->assertEquals('1', $group->getList()[0]->getId());
        $this->assertEquals('test-1', $group->getList()[0]->getName());

        $this->assertEquals(null, $group->getList()[1]->getName());
    }

    public function testWithAddedId()
    {
        $item = LinkMultipleItem::create('1')->withName('test-1');

        $group = LinkMultiple
            ::create([$item])
            ->withAddedId('2');

        $this->assertEquals(2, $group->getCount());

        $this->assertEquals('1', $group->getList()[0]->getId());
        $this->assertEquals('test-1', $group->getList()[0]->getName());

        $this->assertEquals('2', $group->getList()[1]->getId());
    }

    public function testWithAddedIdList(): void
    {
        $item = LinkMultipleItem::create('1')->withName('test-1');

        $group = LinkMultiple
            ::create([$item])
            ->withAddedIdList(['2', '3']);

        $this->assertEquals(3, $group->getCount());

        $this->assertEquals('1', $group->getList()[0]->getId());
        $this->assertEquals('test-1', $group->getList()[0]->getName());

        $this->assertEquals('2', $group->getList()[1]->getId());
    }

    public function testWithAddedList()
    {
        $group = LinkMultiple
            ::create()
            ->withAddedList([
                LinkMultipleItem::create('1'),
                LinkMultipleItem::create('2'),
            ]);

        $this->assertEquals(2, $group->getCount());
    }

    public function testWithRemoved1()
    {
        $group = LinkMultiple
            ::create([
                LinkMultipleItem::create('1'),
                LinkMultipleItem::create('2'),
                LinkMultipleItem::create('3'),
            ])
            ->withRemoved(LinkMultipleItem::create('1'));

        $this->assertEquals(2, $group->getCount());
    }

    public function testGetById()
    {
        $group = LinkMultiple
            ::create([
                LinkMultipleItem::create('1'),
                LinkMultipleItem::create('2'),
                LinkMultipleItem::create('3'),
            ]);

        $this->assertEquals('1', $group->getById('1')->getId());

        $this->assertNull($group->getById('4'));
    }

    public function testClone()
    {
        $group = LinkMultiple
            ::create([
                LinkMultipleItem::create('1'),
                LinkMultipleItem::create('2'),
                LinkMultipleItem::create('3'),
            ]);

        $cloned = clone $group;

        $this->assertEquals('1', $cloned->getById('1')->getId());

        $this->assertNotSame($cloned->getList()[1], $group->getList()[1]);
    }

    public function testItemColumn()
    {
        $item = LinkMultipleItem
            ::create('1')
            ->withColumnValue('key', 'value');

        $this->assertTrue($item->hasColumnValue('key'));

        $this->assertFalse($item->hasColumnValue('key-bad'));

        $this->assertEquals('value', $item->getColumnValue('key'));

        $this->assertEquals(['key'], $item->getColumnList());
    }
}
