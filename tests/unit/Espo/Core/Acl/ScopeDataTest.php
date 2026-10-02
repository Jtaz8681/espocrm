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

namespace tests\unit\Espo\Core\Acl;

use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;

use InvalidArgumentException;
use RuntimeException;

class ScopeDataTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testBooleanTrue()
    {
        $data = ScopeData::fromRaw(true);

        $this->assertTrue($data->isBoolean());
        $this->assertTrue($data->isTrue());
        $this->assertFalse($data->isFalse());

        $this->assertEquals(Table::LEVEL_NO, $data->getDelete());
    }

    public function testBooleanFalse()
    {
        $data = ScopeData::fromRaw(false);

        $this->assertTrue($data->isBoolean());
        $this->assertTrue($data->isFalse());
        $this->assertFalse($data->isTrue());

        $this->assertEquals(Table::LEVEL_NO, $data->getDelete());
    }

    public function testNotBoolean()
    {
        $data = ScopeData::fromRaw((object) []);

        $this->assertFalse($data->isBoolean());

        $this->assertFalse($data->isTrue());
        $this->assertFalse($data->isFalse());
    }

    public function testInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        ScopeData::fromRaw(null);
    }

    public function testRecord()
    {
        $raw = (object) [
            Table::ACTION_CREATE => Table::LEVEL_YES,
            Table::ACTION_READ => Table::LEVEL_ALL,
            Table::ACTION_EDIT => Table::LEVEL_TEAM,
            Table::ACTION_DELETE => Table::LEVEL_NO,
        ];

        $data = ScopeData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_YES, $data->getCreate());
        $this->assertEquals(Table::LEVEL_ALL, $data->getRead());
        $this->assertEquals(Table::LEVEL_TEAM, $data->getEdit());
        $this->assertEquals(Table::LEVEL_NO, $data->getDelete());

        $this->assertTrue($data->hasNotNo());
    }

    public function testRecordEmpty()
    {
        $raw = (object) [];

        $data = ScopeData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_NO, $data->getDelete());

        $this->assertFalse($data->hasNotNo());
    }

    public function testRecordOnlyNo()
    {
        $raw = (object) [
            Table::ACTION_CREATE => Table::LEVEL_NO,
            Table::ACTION_READ => Table::LEVEL_NO,
            Table::ACTION_EDIT => Table::LEVEL_NO,
            Table::ACTION_DELETE => Table::LEVEL_NO,
        ];

        $data = ScopeData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_NO, $data->getDelete());

        $this->assertFalse($data->hasNotNo());
    }

    public function testAccessingProperty()
    {
        $this->expectException(RuntimeException::class);

        $data = ScopeData::fromRaw(false);

        $data->read;
    }

    public function testBadKey()
    {
        $this->expectException(RuntimeException::class);

        ScopeData::fromRaw((object) [
            Table::ACTION_CREATE => false,
        ]);
    }
}
