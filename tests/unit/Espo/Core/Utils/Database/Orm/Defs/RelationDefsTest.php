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
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;
use PHPUnit\Framework\TestCase;

class RelationDefsTest extends TestCase
{
    public function testWithParamsMerged(): void
    {
        $defs = RelationDefs::create('test')
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

        $defs = RelationDefs::create('test')
            ->withParamsMerged($params);

        $this->assertEquals($params, $defs->toAssoc());
    }

    public function testParams(): void
    {
        $defs = RelationDefs::create('test')
            ->withType(RelationType::MANY_MANY)
            ->withForeignEntityType('Test')
            ->withForeignRelationName('foreign')
            ->withRelationshipName('Name')
            ->withKey('key')
            ->withForeignKey('foreignKey')
            ->withMidKeys('k1', 'k2')
            ->withAdditionalColumn(
                AttributeDefs::create('entityType')
                    ->withType(AttributeType::VARCHAR)
                    ->withLength(100)
            )
            ->withAdditionalColumn(
                AttributeDefs::create('primary')
                    ->withType(AttributeType::BOOL)
                    ->withDefault(false)
            );

        $this->assertEquals(RelationType::MANY_MANY, $defs->getType());
        $this->assertEquals('Test', $defs->getForeignEntityType());
        $this->assertEquals('foreign', $defs->getForeignRelationName());
        $this->assertEquals('Name', $defs->getRelationshipName());
        $this->assertEquals('key', $defs->getKey());
        $this->assertEquals('foreignKey', $defs->getForeignKey());
        $this->assertEquals(['k1', 'k2'], $defs->getParam('midKeys'));

        $params = $defs->toAssoc();

        $this->assertEquals([
            'entityType' => [
                'type' => AttributeType::VARCHAR,
                'len' => 100,
            ],
            'primary' => [
                'type' => AttributeType::BOOL,
                'default' => false,
            ],
        ], $params['additionalColumns']);
    }
}
