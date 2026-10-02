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

namespace tests\unit\Espo\Core\Utils\Database\Orm\Defs;

use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\ORM\Type\AttributeType;
use PHPUnit\Framework\TestCase;

class AttributeDefsTest extends TestCase
{
    public function testWithParamsMerged(): void
    {
        $defs = AttributeDefs::create('test')
            ->withParamsMerged([
                'a' => 'a',
                'b' => 'b',
            ])
            ->withParamsMerged([
                'b' => 'mb',
                'c' => 'mc',
            ])
            ->withParam('e', 'e');

        $this->assertEquals('a', $defs->getParam('a'));
        $this->assertEquals('mb', $defs->getParam('b'));
        $this->assertEquals('mc', $defs->getParam('c'));
        $this->assertEquals('e', $defs->getParam('e'));

        $this->assertTrue($defs->hasParam('c'));
        $this->assertFalse($defs->hasParam('d'));
    }

    public function testToAssoc(): void
    {
        $params = [
            'a' => 'a',
            'b' => 'b',
        ];

        $defs = AttributeDefs::create('test')
            ->withParamsMerged($params);

        $this->assertEquals($params, $defs->toAssoc());
    }

    public function testType(): void
    {
        $defs = AttributeDefs::create('test')->withType(AttributeType::VARCHAR);

        $this->assertEquals(AttributeType::VARCHAR, $defs->getType());
    }

    public function testParams(): void
    {
        $defs = AttributeDefs::create('test')
            ->withNotStorable()
            ->withDefault('1');

        $this->assertEquals([
            'notStorable' => true,
            'default' => '1',
        ], $defs->toAssoc());
    }
}
