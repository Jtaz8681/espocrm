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

use Espo\ORM\BaseEntity;
use Espo\ORM\Defs;
use Espo\ORM\Defs\DefsData;
use Espo\ORM\Entity;
use Espo\ORM\EntityCollection;
use Espo\ORM\Metadata;
use Espo\ORM\MetadataDataProvider;
use Espo\Core\ORM\EntityManager;

use PHPUnit\Framework\TestCase;
use SplObjectStorage;

require_once 'tests/unit/testData/DB/Entities.php';

class CollectionTest extends TestCase
{
    private $metadata;
    private $entityManager;

    protected function setUp(): void
    {
        $ormMetadata = include('tests/unit/testData/DB/ormMetadata.php');

        $metadataDataProvider = $this->createMock(MetadataDataProvider::class);

        $metadataDataProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($ormMetadata);

        $this->metadata = new Metadata($metadataDataProvider);
        $defsData = new DefsData($this->metadata);
        $defs = new Defs($defsData);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getDefs')
            ->willReturn($defs);
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function createEntity(string $entityType): BaseEntity
    {
        $defs = $this->metadata->get($entityType);

        return new BaseEntity($entityType, $defs, $this->entityManager);
    }

    public function testEntityCollectionAppend(): void
    {
        $entity1 = $this->createEntity('Account');
        $entity2 = $this->createEntity('Account');
        $entity3 = $this->createEntity('Account');

        $collection = new EntityCollection([$entity1]);

        $collection[] = $entity2;
        $collection->append($entity3);

        $this->assertEquals(3, $collection->count());
    }

    public function testEntityCollectionIteratorToArray(): void
    {
        $entity1 = $this->createEntity('Account');
        $entity2 = $this->createEntity('Account');
        $entity3 = $this->createEntity('Account');

        $collection1 = new EntityCollection([$entity1]);
        $collection2 = new EntityCollection([$entity2, $entity3]);

        $collection = new EntityCollection([
            ...iterator_to_array($collection1),
            ...iterator_to_array($collection2),
        ]);

        $this->assertEquals(3, $collection->count());
    }

    public function testEntityCollectionUnset(): void
    {
        $entity1 = $this->createEntity('Account');
        $entity2 = $this->createEntity('Account');

        $collection = new EntityCollection([$entity1]);

        $collection[] = $entity2;
        unset($collection[1]);

        $this->assertEquals(1, $collection->count());
    }

    public function testFilter(): void
    {
        $e1 = $this->createMock(Entity::class);
        $e2 = $this->createMock(Entity::class);
        $e3 = $this->createMock(Entity::class);
        $e4 = $this->createMock(Entity::class);

        $collection = new EntityCollection([$e1, $e2, $e3, $e4], 'Account');

        $filtered = $collection->filter(function ($e) use ($e2, $e3) {
            return $e !== $e2 && $e !== $e3;
        });

        $this->assertEquals([$e1, $e2], [...$filtered]);
        $this->assertEquals($collection->getEntityType(), $filtered->getEntityType());
    }

    public function testSort(): void
    {
        $e1 = $this->createMock(Entity::class);
        $e2 = $this->createMock(Entity::class);
        $e3 = $this->createMock(Entity::class);

        $map = new SplObjectStorage();

        $map[$e1] = 3;
        $map[$e2] = 2;
        $map[$e3] = 1;

        $collection = new EntityCollection([$e1, $e2, $e3], 'Account');

        $sorted = $collection->sort(function ($e1, $e2) use ($map) {
            return $map[$e1] - $map[$e2];
        });

        $this->assertEquals([$e3, $e2, $e1], [...$sorted]);
        $this->assertEquals($collection->getEntityType(), $sorted->getEntityType());
    }

    public function testFind(): void
    {
        $e1 = $this->createMock(Entity::class);
        $e2 = $this->createMock(Entity::class);
        $e3 = $this->createMock(Entity::class);

        $collection = new EntityCollection([$e1, $e2, $e3]);

        $e = $collection->find(function ($e) use ($e2) {
            return $e === $e2;
        });

        $this->assertSame($e2, $e);

        $e = $collection->find(function ($e) use ($e2) {
            return $e === 0;
        });

        $this->assertNull($e);
    }

    public function testReverse(): void
    {
        $e1 = $this->createMock(Entity::class);
        $e2 = $this->createMock(Entity::class);
        $e3 = $this->createMock(Entity::class);

        $collection = new EntityCollection([$e1, $e2, $e3], 'Account');

        $reversed = $collection->reverse();

        $this->assertEquals([$e3, $e2, $e1], [...$reversed]);
        $this->assertEquals($collection->getEntityType(), $reversed->getEntityType());
    }
}
