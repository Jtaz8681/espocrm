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

namespace tests\unit\Espo\Core\Field\EmailAddress;

use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\EmailAddressGroup;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailAddressGroupTest extends TestCase
{
    public function testEmpty()
    {
        $group = EmailAddressGroup::create();

        $this->assertEquals(0, count($group->getAddressList()));
        $this->assertEquals(0, count($group->getSecondaryList()));

        $this->assertNull($group->getPrimary());
        $this->assertNull($group->getPrimaryAddress());

        $this->assertEquals(0, $group->getCount());
    }

    public function testDuplicate1()
    {
        $this->expectException(InvalidArgumentException::class);

        EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('one@test.com'),
            ]);
    }

    public function testDuplicate2()
    {
        $this->expectException(InvalidArgumentException::class);

        EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('ONE@test.com'),
            ]);
    }

    public function testWithPrimary1()
    {
        $primary = EmailAddress::create('primary@test.com')->invalid();

        $group = EmailAddressGroup
            ::create()
            ->withPrimary($primary);

        $this->assertEquals(1, count($group->getList()));
        $this->assertEquals(0, count($group->getSecondaryList()));

        $this->assertNotNull($group->getPrimary());

        $this->assertEquals('primary@test.com', $group->getPrimary()->getAddress());
        $this->assertEquals('primary@test.com', $group->getPrimaryAddress());

        $primaryAnother = EmailAddress::create('primaryAnother@test.com');

        $groupAnother = $group->withPrimary($primaryAnother);

        $this->assertEquals(2, count($groupAnother->getList()));
        $this->assertEquals(1, count($groupAnother->getSecondaryList()));

        $this->assertEquals('primaryAnother@test.com', $groupAnother->getPrimary()->getAddress());


        $this->assertEquals('primary@test.com', $groupAnother->getList()[1]->getAddress());
        $this->assertTrue($groupAnother->getList()[1]->isInvalid());

        $this->assertTrue($groupAnother->hasAddress('primary@test.com'));
        $this->assertTrue($groupAnother->hasAddress('primaryAnother@test.com'));
    }

    public function testWithPrimary2()
    {
        $address = EmailAddress::create('one@test.com')->invalid();

        $group = EmailAddressGroup
            ::create([$address])
            ->withAdded(
                EmailAddress::create('two@test.com')
            )
            ->withPrimary(
                EmailAddress::create('two@test.com')
            );

        $this->assertEquals('two@test.com', $group->getPrimary()->getAddress());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithAdded1()
    {
        $address = EmailAddress::create('one@test.com')->invalid();

        $group = EmailAddressGroup
            ::create([$address])
            ->withAdded(
                EmailAddress::create('two@test.com')
            );

        $this->assertEquals('one@test.com', $group->getPrimary()->getAddress());

        $this->assertEquals(2, count($group->getList()));

        $this->assertEquals(2, $group->getCount());
    }

    public function testWithAddedList()
    {
        $group = EmailAddressGroup
            ::create()
            ->withAddedList([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
            ]);

        $this->assertEquals('one@test.com', $group->getPrimary()->getAddress());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved1()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
                EmailAddress::create('three@test.com'),
            ])
            ->withRemoved(EmailAddress::create('two@test.com'));

        $this->assertEquals('one@test.com', $group->getPrimary()->getAddress());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved2()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
                EmailAddress::create('three@test.com'),
            ])
            ->withRemoved(EmailAddress::create('one@test.com'));

        $this->assertEquals('two@test.com', $group->getPrimary()->getAddress());

        $this->assertEquals(2, count($group->getList()));
    }

    public function testWithRemoved3()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
            ])
            ->withRemoved(EmailAddress::create('one@test.com'));

        $this->assertNull($group->getPrimary());

        $this->assertEquals(0, count($group->getList()));

        $this->assertEquals(0, $group->getCount());
    }

    public function testHasAddress()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
            ]);

        $this->assertTrue($group->hasAddress('one@test.com'));
        $this->assertTrue($group->hasAddress('two@test.com'));

        $this->assertFalse($group->hasAddress('four@test.com'));
    }

    public function testGetByAddress()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
                EmailAddress::create('three@test.com'),
            ]);

        $this->assertEquals('one@test.com', $group->getByAddress('one@test.com')->getAddress());

        $this->assertNull($group->getByAddress('four@test.com'));
    }

    public function testClone()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('one@test.com'),
                EmailAddress::create('two@test.com'),
                EmailAddress::create('three@test.com'),
            ]);

        $cloned = clone $group;

        $this->assertEquals('one@test.com', $cloned->getByAddress('one@test.com')->getAddress());

        $this->assertEquals($cloned->getPrimary()->getAddress(), $group->getPrimary()->getAddress());

        $this->assertNotSame($cloned->getPrimary(), $group->getPrimary());

        $this->assertNotSame($cloned->getList()[1], $group->getList()[1]);
    }
}
