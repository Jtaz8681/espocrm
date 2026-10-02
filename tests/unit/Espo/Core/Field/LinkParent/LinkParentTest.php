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

namespace tests\unit\Espo\Core\Field\LinkParent;

use Espo\Core\Field\LinkParent;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LinkParentTest extends TestCase
{
    public function testCreate()
    {
        $value = LinkParent::create('Test', 'id');

        $this->assertEquals('Test', $value->getEntityType());
        $this->assertEquals('id', $value->getId());
        $this->assertEquals(null, $value->getName());
    }

    public function testBad1()
    {
        $this->expectException(InvalidArgumentException::class);

        LinkParent::create('Test', '');
    }

    public function testBad2()
    {
        $this->expectException(InvalidArgumentException::class);

        LinkParent::create('', 'id');
    }

    public function testWithName()
    {
        $value = LinkParent::create('Test', 'id')->withName('Name');

        $this->assertEquals('Name', $value->getName());
    }
}
