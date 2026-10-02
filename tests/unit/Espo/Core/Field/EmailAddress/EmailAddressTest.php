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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    public function testInvalidAddress()
    {
        $this->expectException(InvalidArgumentException::class);

        EmailAddress::create('one');
    }

    public function testInvalidEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        EmailAddress::create('');
    }

    public function testCloneInvalid()
    {
        $address = EmailAddress::create('test@test.com')->invalid();

        $this->assertEquals('test@test.com', $address->getAddress());

        $this->assertTrue($address->isInvalid());
        $this->assertFalse($address->isOptedOut());
    }

    public function testCloneOptedOut()
    {
        $address = EmailAddress::create('test@test.com')->optedOut();

        $this->assertFalse($address->isInvalid());
        $this->assertTrue($address->isOptedOut());
    }

    public function testCloneNotOptedOut()
    {
        $address = EmailAddress::create('test@test.com')
            ->optedOut()
            ->notOptedOut()
            ->invalid()
            ->notInvalid();

        $this->assertFalse($address->isInvalid());
        $this->assertFalse($address->isOptedOut());
    }
}
