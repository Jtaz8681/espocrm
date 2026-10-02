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

namespace tests\unit\Espo\Core\Select\Where;

use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\Scanner;
use Espo\ORM\BaseEntity as Entity;
use Espo\ORM\Entity as OrmEntity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Select as Query;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use Espo\ORM\Type\RelationType;
use PHPUnit\Framework\TestCase;

class ScannerTest extends TestCase
{
    private $scanner;
    private $queryBuilder;
    private $entity;

    protected function setUp() : void
    {
        $entityManager = $this->createMock(EntityManager::class);
        $this->scanner = new Scanner($entityManager);
        $entityType = 'Test';
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $query = $this->createMock(Query::class);

        $query
            ->expects($this->any())
            ->method('getFrom')
            ->willReturn($entityType);

        $this->queryBuilder
            ->expects($this->any())
            ->method('build')
            ->willReturn($query);

        $this->entity = $this->createMock(Entity::class);

        $entityManager
            ->expects($this->any())
            ->method('getNewEntity')
            ->with($entityType)
            ->willReturn($this->entity);
    }

    public function testApplyLeftJoins1(): void
    {
        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'TEST:(link2.test2)',
                    'value' => 'value',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'link3.test3',
                    'value' => 'value',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'test4',
                    'value' => 'value',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->any())
            ->method('hasRelation')
            ->willReturnMap(
                [
                    ['link1', true],
                    ['link2', true],
                    ['link3', true],
                ]
            );

        $c = $this->exactly(3);

        $this->queryBuilder
            ->expects($c)
            ->method('leftJoin')
            ->willReturnCallback(function ($link) use ($c) {
                if ($c->numberOfInvocations() === 1) {
                    $this->assertEquals('link2', $link);
                }

                if ($c->numberOfInvocations() === 2) {
                    $this->assertEquals('link3', $link);
                }

                if ($c->numberOfInvocations() === 3) {
                    $this->assertEquals('link4', $link);
                }

                return $this->queryBuilder;
            });

        /*$this->queryBuilder
            ->expects($this->once())
            ->method('distinct');*/

        $this->entity
            ->expects($this->any())
            ->method('getAttributeType')
            ->willReturnMap(
                [
                    ['test1', OrmEntity::VARCHAR],
                    ['test4', OrmEntity::FOREIGN],
                ]
            );

        $this->entity
            ->expects($this->any())
            ->method('getRelationType')
            ->willReturnMap(
                [
                    ['link2', OrmEntity::HAS_MANY],
                    ['link3', OrmEntity::BELONGS_TO],
                ]
            );

        $this->entity
            ->expects($this->any())
            ->method('getAttributeParam')
            ->willReturnMap(

                    [
                        ['test4', 'relation', 'link4'],
                    ]

            );

        $this->scanner->apply($this->queryBuilder, $item);
    }

    public function testHasRelatedMany(): void
    {
        $em = $this->createMock(EntityManager::class);
        $entity = $this->createMock(Entity::class);

        $em->expects($this->once())
            ->method('getNewEntity')
            ->willReturn($entity);

        $entity
            ->expects($this->any())
            ->method('hasRelation')
            ->willReturnMap(
                [
                    ['link2', true],
                    ['link3', true],
                ]
            );

        $entity
            ->expects($this->any())
            ->method('getRelationType')
            ->willReturnMap(
                [
                    ['link2', RelationType::HAS_MANY],
                    ['link3', RelationType::BELONGS_TO],
                ]
            );

        $scanner = new Scanner($em);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'TEST:(link2.test2)',
                    'value' => 'value',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'link3.test3',
                    'value' => 'value',
                ],
            ],
        ]);

        $this->assertTrue($scanner->hasRelatedMany('Test', $item));

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'link3.test3',
                    'value' => 'value',
                ],
            ],
        ]);

        $this->assertFalse($scanner->hasRelatedMany('Test', $item));
    }
}
