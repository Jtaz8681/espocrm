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

use Espo\Core\{
    Acl\FieldData,
    Acl\Table,
};

class FieldDataTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testGet1(): void
    {
        $raw = (object) [
            Table::ACTION_EDIT => Table::LEVEL_YES,
            Table::ACTION_READ => Table::LEVEL_NO,
        ];

        $data = FieldData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_NO, $data->getRead());
        $this->assertEquals(Table::LEVEL_YES, $data->getEdit());
    }

    public function testGet2(): void
    {
        $raw = (object) [
            Table::ACTION_EDIT => Table::LEVEL_NO,
            Table::ACTION_READ => Table::LEVEL_YES,
        ];

        $data = FieldData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_YES, $data->getRead());
        $this->assertEquals(Table::LEVEL_NO, $data->getEdit());
    }

    public function testGetEmpty(): void
    {
        $raw = (object) [
        ];

        $data = FieldData::fromRaw($raw);

        $this->assertEquals(Table::LEVEL_NO, $data->getRead());
        $this->assertEquals(Table::LEVEL_NO, $data->getEdit());
    }
}
