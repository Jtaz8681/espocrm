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

namespace tests\unit\Espo\Core\Field\Link;

use Espo\Core\Field\Link;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class LinkTest extends TestCase
{
    public function testCreate()
    {
        $value = Link::create('id');

        $this->assertEquals('id', $value->getId());
        $this->assertEquals(null, $value->getName());
    }

    public function testBad1()
    {
        $this->expectException(InvalidArgumentException::class);

        Link::create('');
    }

    public function testWithName()
    {
        $value = Link::create('id')->withName('Name');

        $this->assertEquals('Name', $value->getName());
    }
}
