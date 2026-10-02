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
use PHPUnit\Framework\Attributes\DataProvider;

#[NoTransaction]
class AddressFieldTest extends Base
{
    static public function fieldList()
    {
        return [
            ['testAddressStreet', 255],
            ['testAddressCity', 100],
            ['testAddressState', 100],
            ['testAddressCountry', 100],
            ['testAddressPostalCode', 40],
        ];
    }

    #[DataProvider('fieldList')]
    public function testColumn($fieldName, $length)
    {
        $column = $this->getColumnInfo('Test', $fieldName);

        $this->assertNotEmpty($column);
        $this->assertEquals('varchar', $column['DATA_TYPE']);
        $this->assertEquals($length, $column['CHARACTER_MAXIMUM_LENGTH']);
        $this->assertEquals('YES', $column['IS_NULLABLE']);
        $this->assertEquals('utf8mb4_unicode_ci', $column['COLLATION_NAME']);
    }
}
