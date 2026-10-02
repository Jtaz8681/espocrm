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

namespace tests\unit\Espo\Core\Field\PhoneNumber;

use Espo\Core\Field\PhoneNumber;
use Espo\Core\Field\PhoneNumberGroup;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class PhoneNumberGroupTest extends TestCase
{
    public function testEmpty()
    {
        $group = PhoneNumberGroup::create();

        $this->assertEquals(0, count($group->getNumberList()));
        $this->assertEquals(0, count($group->getSecondaryList()));

        $this->assertNull($group->getPrimary());
        $this->assertNull($group->getPrimaryNumber());

        $this->assertEquals(0, $group->getCount());
    }

    public function testDuplicate()
    {
        $this->expectException(InvalidArgumentException::class);

        PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+100'),
            ]);
    }

    public function testWithPrimary1()
    {
        $primary = PhoneNumber::create('+000')->invalid();

        $group = PhoneNumberGroup
            ::create()
            ->withPrimary($primary);

        $this->assertEquals(1, count($group->getList()));
        $this->assertEquals(0, count($group->getSecondaryList()));

        $this->assertNotNull($group->getPrimary());

        $this->assertEquals('+000', $group->getPrimary()->getNumber());
        $this->assertEquals('+000', $group->getPrimaryNumber());

        $primaryAnother = PhoneNumber::create('+001');

        $groupAnother = $group->withPrimary($primaryAnother);

        $this->assertEquals(2, count($groupAnother->getList()));
        $this->assertEquals(1, count($groupAnother->getSecondaryList()));

        $this->assertEquals('+001', $groupAnother->getPrimary()->getNumber());

        $this->assertEquals('+000', $groupAnother->getList()[1]->getNumber());
        $this->assertTrue($groupAnother->getList()[1]->isInvalid());

        $this->assertTrue($groupAnother->hasNumber('+000'));
        $this->assertTrue($groupAnother->hasNumber('+001'));
    }

    public function testWithPrimary2()
    {
        $number = PhoneNumber::create('+100')->invalid();

        $group = PhoneNumberGroup
            ::create([$number])
            ->withAdded(
                PhoneNumber::create('+200')
            )
            ->withPrimary(
                PhoneNumber::create('+200')
            );

        $this->assertEquals('+200', $group->getPrimary()->getNumber());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithAdded1()
    {
        $number = PhoneNumber::create('+100')->invalid();

        $group = PhoneNumberGroup
            ::create([$number])
            ->withAdded(
                PhoneNumber::create('+200')
            );

        $this->assertEquals('+100', $group->getPrimary()->getNumber());

        $this->assertEquals(2, count($group->getList()));

        $this->assertEquals(2, $group->getCount());
    }

    public function testWithAddedList()
    {
        $group = PhoneNumberGroup
            ::create()
            ->withAddedList([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
            ]);

        $this->assertEquals('+100', $group->getPrimary()->getNumber());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved1()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
                PhoneNumber::create('+300'),
            ])
            ->withRemoved(PhoneNumber::create('+200'));

        $this->assertEquals('+100', $group->getPrimary()->getNumber());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved2()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
                PhoneNumber::create('+300'),
            ])
            ->withRemoved(PhoneNumber::create('+100'));

        $this->assertEquals('+200', $group->getPrimary()->getNumber());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved3()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
            ])
            ->withRemoved(PhoneNumber::create('+100'));

        $this->assertNull($group->getPrimary());

        $this->assertEquals(0, count($group->getList()));

        $this->assertEquals(0, $group->getCount());
    }

    public function testHasNumber()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
            ]);

        $this->assertTrue($group->hasNumber('+100'));
        $this->assertTrue($group->hasNumber('+200'));

        $this->assertFalse($group->hasNumber('+400'));
    }

    public function testGetByNumber()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
                PhoneNumber::create('+300'),
            ]);

        $this->assertEquals('+100', $group->getByNumber('+100')->getNumber());

        $this->assertNull($group->getByNumber('+400'));
    }

    public function testClone()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+100'),
                PhoneNumber::create('+200'),
                PhoneNumber::create('+300'),
            ]);

        $cloned = clone $group;

        $this->assertEquals('+100', $cloned->getByNumber('+100')->getNumber());

        $this->assertEquals($cloned->getPrimary()->getNumber(), $group->getPrimary()->getNumber());

        $this->assertNotSame($cloned->getPrimary(), $group->getPrimary());

        $this->assertNotSame($cloned->getList()[1], $group->getList()[1]);
    }
}
