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

namespace tests\unit\Espo\Core\Select\Bool;

use Espo\Core\Select\Bool\Filters\OnlyMy;
use Espo\Core\Select\Bool\Filters\Shared;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Entities\User;
use Espo\ORM\Defs;
use Espo\ORM\Defs\EntityDefs;
use Espo\ORM\Defs\RelationDefs;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Type\RelationType;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    public function testOnlyMyUsesMiddleConditions(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn('user-id');
        $user->method('isPortal')->willReturn(false);

        $fieldHelper = $this->createMock(FieldHelper::class);
        $fieldHelper->method('hasAssignedUsersField')->willReturn(true);

        $filter = new OnlyMy('Test', $user, $fieldHelper, $this->createDefs('assignedUsers', 'entityUser'));

        $joinConditions = $this->applyAndGetJoinConditions($filter);

        $this->assertEquals('Test', $joinConditions['assignedUsersMiddle.entityType'] ?? null);
    }

    public function testSharedUsesMiddleConditions(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn('user-id');

        $fieldHelper = $this->createMock(FieldHelper::class);
        $fieldHelper->method('hasCollaboratorsField')->willReturn(true);

        $filter = new Shared('Test', $user, $fieldHelper, $this->createDefs('collaborators', 'entityCollaborator'));

        $joinConditions = $this->applyAndGetJoinConditions($filter);

        $this->assertEquals('Test', $joinConditions['collaboratorsMiddle.entityType'] ?? null);
    }

    private function createDefs(string $link, string $relationName): Defs
    {
        $relationDefs = RelationDefs::fromRaw([
            'type' => RelationType::MANY_MANY,
            'entity' => User::ENTITY_TYPE,
            'relationName' => $relationName,
            'midKeys' => ['entityId', 'userId'],
            'conditions' => ['entityType' => 'Test'],
        ], $link);

        $entityDefs = $this->createMock(EntityDefs::class);
        $entityDefs->method('getRelation')->with($link)->willReturn($relationDefs);

        $defs = $this->createMock(Defs::class);
        $defs->method('getEntity')->with('Test')->willReturn($entityDefs);

        return $defs;
    }

    /**
     * @return array<string, mixed>
     */
    private function applyAndGetJoinConditions(OnlyMy|Shared $filter): array
    {
        $orGroupBuilder = new OrGroupBuilder();

        $filter->apply(SelectBuilder::create()->from('Test'), $orGroupBuilder);

        $rawValue = $orGroupBuilder->build()->getRawValue();

        $subQuery = $rawValue['id=s'] ?? null;

        $this->assertInstanceOf(Select::class, $subQuery);

        $joins = $subQuery->getRaw()['joins'] ?? [];

        $this->assertCount(1, $joins);

        return $joins[0][2];
    }
}
