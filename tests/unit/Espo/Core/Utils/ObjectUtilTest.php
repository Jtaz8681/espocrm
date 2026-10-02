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

namespace tests\Espo\Core\Utils;

use Espo\Core\Utils\ObjectUtil;

class ObjectUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testClone1()
    {
        $original = (object) [
            'key1' => '1',
            'key2' => (object) [
                'key21' => [
                    '211',
                    '212',
                    (object) [
                        '2111' => '1',
                    ],
                ],
            ],
            'key3' => [
                '31',
                '32',
                null,
            ],
            'key4' => null,
        ];

        $cloned = ObjectUtil::clone($original);

        $this->assertEquals($cloned, $original);

        $this->assertNotSame($cloned, $original);

        $this->assertNotSame($cloned->key2, $original->key2);

        $this->assertNotSame($cloned->key2->key21[2], $original->key2->key21[2]);
    }
}

