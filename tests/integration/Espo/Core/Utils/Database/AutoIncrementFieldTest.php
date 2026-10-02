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
class AutoIncrementFieldTest extends Base
{
    public function testColumn()
    {
        $column = $this->getColumnInfo('Case', 'number');

        $this->assertNotEmpty($column);
        $this->assertEquals('int', $column['DATA_TYPE']);
        $this->assertEquals('NO', $column['IS_NULLABLE']);
        $this->assertEquals('10', $column['NUMERIC_PRECISION']);
        $this->assertEquals('auto_increment', $column['EXTRA']);
        $this->assertEquals('UNI', $column['COLUMN_KEY']);
    }

    public function testColumnOnExistingTable(): void
    {
        $this->updateDefs('Test', 'testAutoIncrement', [
            'type' => 'autoincrement',
        ]);

        $column = $this->getColumnInfo('Test', 'testAutoIncrement');

        $this->assertNotEmpty($column);
        $this->assertEquals('int', $column['DATA_TYPE']);
        $this->assertEquals('NO', $column['IS_NULLABLE']);
        $this->assertEquals('10', $column['NUMERIC_PRECISION']);
        $this->assertEquals('auto_increment', $column['EXTRA']);
        $this->assertEquals('UNI', $column['COLUMN_KEY']);
    }

    public function testDeleteColumnOnExistingTable(): void
    {
        // 1. Create "testAutoIncrement" field
        $this->testColumnOnExistingTable();

        // 2. Delete "testAutoIncrement" field
        $this->getMetadata()->delete('entityDefs', 'Test', ['fields.testAutoIncrement']);
        $this->getMetadata()->save();

        // Issue that it requires rebuilding between removing and adding a new indexes.
        $this->getDataManager()->rebuild();

        $column = $this->getColumnInfo('Test', 'testAutoIncrement');

        $this->assertNotEmpty($column);
        $this->assertEquals('int', $column['DATA_TYPE']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('10', $column['NUMERIC_PRECISION']);
        $this->assertEmpty($column['EXTRA']);
        $this->assertEmpty($column['COLUMN_KEY']);
    }

    public function testDeleteCreateColumnOnExistingTable(): void
    {
        // 1. Create "testAutoIncrement" field
        $this->testColumnOnExistingTable();

        // 2. Delete "testAutoIncrement" field
        $metadata = $this->getMetadata();
        $metadata->delete('entityDefs', 'Test', ['fields.testAutoIncrement']);
        $metadata->save();

        $this->getDataManager()->rebuild();

        // 3. Create "testAutoIncrement2" field
        $this->updateDefs('Test', 'testAutoIncrement2', [
            'type' => 'autoincrement',
        ]);

        $column = $this->getColumnInfo('Test', 'testAutoIncrement');
        $this->assertNotEmpty($column);
        $this->assertEquals('int', $column['DATA_TYPE']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('10', $column['NUMERIC_PRECISION']);
        $this->assertEmpty($column['EXTRA']);
        $this->assertEmpty($column['COLUMN_KEY']);

        $column2 = $this->getColumnInfo('Test', 'testAutoIncrement2');
        $this->assertNotEmpty($column2);
        $this->assertEquals('int', $column2['DATA_TYPE']);
        $this->assertEquals('NO', $column2['IS_NULLABLE']);
        $this->assertEquals('10', $column2['NUMERIC_PRECISION']);
        $this->assertEquals('auto_increment', $column2['EXTRA']);
        $this->assertEquals('UNI', $column2['COLUMN_KEY']);
    }
}
