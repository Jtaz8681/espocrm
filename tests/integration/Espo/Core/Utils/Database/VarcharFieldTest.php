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

use Espo\Core\Utils\Database\Helper as DatabaseHelper;
use integration\Core\NoTransaction;

#[NoTransaction]
class VarcharFieldTest extends Base
{
    public function testColumn()
    {
        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals(100, $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }

    public function testIncreaseColumnLength()
    {
        $this->updateDefs('Test', 'testVarchar', [
            'maxLength' => 150,
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('150', $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }

    public function testReduceColumnLength()
    {
        $this->updateDefs('Test', 'testVarchar', [
            'maxLength' => 50,
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('100', $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }

    public function testReduceColumnLength2()
    {
        $this->updateDefs('Test', 'testVarchar', [
            'maxLength' => 50,
            'default' => 'test-default',
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('100', $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }

    public function testReduceColumnLength3()
    {
        $this->executeQuery(
            "ALTER TABLE test MODIFY COLUMN test_varchar VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;"
        );

        $this->updateDefs('Test', 'testVarchar', [
            'maxLength' => 50,
            'default' => 'test-default',
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('100', $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);

        $this->assertContains($column['COLLATION_NAME'], [
            'utf8_unicode_ci',
            'utf8mb3_unicode_ci'
        ]);
    }

    public function testCollationForExistingColumn()
    {
        $column = $this->getColumnInfo('Test', 'testVarchar');
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);

        $this->executeQuery(
            "ALTER TABLE test MODIFY COLUMN test_varchar VARCHAR(". $column['CHARACTER_MAXIMUM_LENGTH'] .") CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;"
        );

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertContains($column['COLLATION_NAME'], [
            'utf8_unicode_ci',
            'utf8mb3_unicode_ci'
        ]);

        $this->updateDefs('Test', 'testVarchar', [
            'maxLength' => 150,
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('150', $column['CHARACTER_MAXIMUM_LENGTH']);

        $this->assertContains($column['COLLATION_NAME'], [
            'utf8_unicode_ci',
            'utf8mb3_unicode_ci'
        ]);
    }

    public function testCollationForExistingColumn2()
    {
        $column = $this->getColumnInfo('Test', 'testVarchar');
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);

        $this->executeQuery(
            "ALTER TABLE test MODIFY COLUMN test_varchar VARCHAR(". $column['CHARACTER_MAXIMUM_LENGTH'] .") CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;"
        );

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertContains($column['COLLATION_NAME'], [
            'utf8_unicode_ci',
            'utf8mb3_unicode_ci'
        ]);

        $this->updateDefs('Test', 'testVarchar', [
            'default' => 'test-default',
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertEquals('varchar', $column['DATA_TYPE']);

        $this->assertContains($column['COLLATION_NAME'], [
            'utf8_unicode_ci',
            'utf8mb3_unicode_ci'
        ]);
    }

    public function testCollationForNewColumn()
    {
        $this->updateDefs('Test', 'newTestVarchar', [
            'type' => 'varchar',
        ]);

        $column = $this->getColumnInfo('Test', 'newTestVarchar');

        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }

    public function testDefaultValue()
    {
        $this->updateDefs('Test', 'testVarchar', [
            'default' => 'test-default',
        ]);

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals('100', $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);

        $dbHelper = $this->getInjectableFactory()->create(DatabaseHelper::class);

        if (
            $dbHelper->getType() == 'MariaDB'
            && version_compare($dbHelper->getVersion(), '10.2.7', '>=')
        ) {
            $this->assertEquals("'test-default'", $column['COLUMN_DEFAULT']);
        } else {
            $this->assertEquals('test-default', $column['COLUMN_DEFAULT']);
        }
    }

    /**
     * Make sure columns not removed.
     */
    public function testRemoveField(): void
    {
        $this->getMetadata()->delete('entityDefs', 'Test', ['fields.testVarchar']);
        $this->getMetadata()->save();
        $this->getDataManager()->rebuildDatabase();

        $column = $this->getColumnInfo('Test', 'testVarchar');

        $this->assertTrue((bool) $column);
    }
}
