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

namespace tests\unit\Espo\ORM;

use Espo\ORM\Type\AttributeType;
use Espo\ORM\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testAreValuesEqualScalar(): void
    {
        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::VARCHAR,
                'a1',
                'a1'
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::VARCHAR,
                1,
                1
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::VARCHAR,
                1.1,
                1.1
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::VARCHAR,
                'a1',
                'a2'
            )
        );
    }

    public function testAreValuesEqualArray(): void
    {
        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                ['a1', 'a2'],
                ['a1', 'a2'],
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                ['a1', 'a2'],
                ['a2', 'a1'],
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                ['a1', 'a2'],
                ['a1', 'a2'],
                isUnordered: true,
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                ['a1', 'a2'],
                ['a2', 'a1'],
                isUnordered: true,
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                ['a1', 'a2'],
                ['a2', 'a1'],
                isUnordered: true,
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                [['a1'], ['a2']],
                [['a1'], ['a2']],
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                [(object) ['a1' => 1], (object) ['a2' => 1]],
                [(object) ['a1' => 1], (object) ['a2' => 1]],
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                [(object) ['a1' => 1], (object) ['a2' => 1]],
                [(object) ['a1' => 2], (object) ['a2' => 1]],
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                [[(object) ['a1' => 1], (object) ['a2' => 1]]],
                [[(object) ['a1' => 1], (object) ['a2' => 1]]],
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::JSON_ARRAY,
                [[(object) ['a1' => 1], (object) ['a2' => 1]]],
                [[(object) ['a1' => 2], (object) ['a2' => 1]]],
            )
        );
    }

    public function testAreValuesEqualObject(): void
    {
        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_OBJECT,
                (object) ['a1' => 1],
                (object) ['a1' => 1],
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::JSON_OBJECT,
                (object) ['a1' => 1],
                (object) ['a1' => 2],
            )
        );

        $this->assertFalse(
            Util::areValuesEqual(
                AttributeType::JSON_OBJECT,
                (object) ['a1' => 1],
                (object) ['a2' => 2],
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_OBJECT,
                (object) ['a1' => 1, 'a2' => 2],
                (object) ['a2' => 2, 'a1' => 1],
            )
        );

        $this->assertTrue(
            Util::areValuesEqual(
                AttributeType::JSON_OBJECT,
                (object) ['a1' => [1, 2], 'a2' => [3, 4]],
                (object) ['a1' => [1, 2], 'a2' => [3, 4]],
            )
        );
    }
}
