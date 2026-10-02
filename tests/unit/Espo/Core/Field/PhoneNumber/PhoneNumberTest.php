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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testInvalidEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        PhoneNumber::create('');
    }

    public function testCloneInvalid()
    {
        $number = PhoneNumber::create('+100')->invalid();

        $this->assertEquals('+100', $number->getNumber());

        $this->assertTrue($number->isInvalid());
        $this->assertFalse($number->isOptedOut());

        $this->assertNull($number->getType());
    }

    public function testCloneOptedOut()
    {
        $number = PhoneNumber::create('+100')->optedOut();

        $this->assertFalse($number->isInvalid());
        $this->assertTrue($number->isOptedOut());
    }

    public function testCloneWithType()
    {
        $number = PhoneNumber::createWithType('+100', 'Office');

        $this->assertEquals('+100', $number->getNumber());
        $this->assertEquals('Office', $number->getType());
    }

    public function testCloneNotOptedOut()
    {
        $number = PhoneNumber::create('+100')
            ->optedOut()
            ->notOptedOut()
            ->invalid()
            ->notInvalid();

        $this->assertFalse($number->isInvalid());
        $this->assertFalse($number->isOptedOut());
    }
}
