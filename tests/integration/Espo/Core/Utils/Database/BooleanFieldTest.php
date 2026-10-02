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

namespace tests\integration\Espo\Core\Utils\Database;

use integration\Core\NoTransaction;

#[NoTransaction]
class BooleanFieldTest extends Base
{
    public function testColumn()
    {
        $column = $this->getColumnInfo('Test', 'testBoolean');

        $this->assertNotEmpty($column);
        $this->assertEquals('tinyint', $column['DATA_TYPE']);
        $this->assertEquals('0', $column['COLUMN_DEFAULT']);
        $this->assertEquals('NO', $column['IS_NULLABLE']);
    }

    public function testDefaultValue()
    {
        $this->updateDefs('Test', 'testBoolean', [
            'default' => true,
        ]);

        $column = $this->getColumnInfo('Test', 'testBoolean');

        $this->assertNotEmpty($column);
        $this->assertEquals('tinyint', $column['DATA_TYPE']);
        $this->assertEquals('1', $column['COLUMN_DEFAULT']);
        $this->assertEquals('NO', $column['IS_NULLABLE']);
    }
}
