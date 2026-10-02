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

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\Helpers\EntityHelper;
use Espo\Core\Select\Where\Checker;
use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\Params;
use Espo\ORM\BaseEntity as Entity;
use Espo\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class CheckerTest extends TestCase
{
    /** @var Checker|null */
    protected $checker = null;
    protected ?EntityManager $entityManager = null;
    /** @var Acl|null  */
    protected $acl = null;

    protected ?EntityHelper $entityHelper = null;

    protected ?string $entityType = null;
    protected ?string $foreignEntityType = null;

    private $entity;
    private $params;

    protected function setUp() : void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->acl = $this->createMock(Acl::class);
        $systemRestriction = $this->createMock(Acl\SystemRestriction::class);
        $this->entityHelper = $this->createMock(EntityHelper::class);

        $systemRestriction
            ->method('checkAttributeRead')
            ->willReturn(true);

        $systemRestriction
            ->method('checkLinkRead')
            ->willReturn(true);

        $systemRestriction
            ->method('checkFieldRead')
            ->willReturn(true);

        $this->entityType = 'Test';
        $this->foreignEntityType = 'TestForeign';

        $this->checker = new Checker(
            entityType: $this->entityType,
            entityManager: $this->entityManager,
            acl: $this->acl,
            systemRestriction: $systemRestriction,
            entityHelper: $this->entityHelper,
        );

        $this->params = $this->createMock(Params::class);
        $this->entity = $this->createMock(Entity::class);
        $foreignEntity = $this->createMock(Entity::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getNewEntity')
            ->willReturnMap([
                [$this->entityType, $this->entity],
                [$this->foreignEntityType, $foreignEntity],
            ]);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testAttributeExistence1()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(false);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value1',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'TEST:(test2)',
                    'value' => 'value2',
                ],
                [
                    'type' => 'linkedWith',
                    'attribute' => 'test3',
                    'value' => 'value3',
                ],
            ],
        ]);

        $this->entity
            ->expects(self::any())
            ->method('hasAttribute')
            ->willReturnMap([
                ['test1', true],
                ['test2', true],
            ]);

        $this->entityHelper
            ->method('getRelationEntityType')
            ->willReturnMap([
                [$this->entity, 'test3', 'AnotherEntity'],
            ]);

        $another = $this->createMock(Entity::class);

        $this->entityManager
            ->method('getEntityById')
            ->willReturnMap([
                ['AnotherEntity', 'value3', $another]
            ]);

        $this->entity
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testAttributeExistence2()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(false);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value1',
                ],
            ],
        ]);

        $this->entity
            ->expects(self::any())
            ->method('hasAttribute')
            ->willReturnMap([
                ['test1', false]
            ]);

        $this->expectException(BadRequest::class);

        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testAttributeExistence3()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(false);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'linkedWith',
                    'attribute' => 'test3',
                    'value' => 'value3',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->once())
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(false);

        $this->expectException(BadRequest::class);

        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testPermissions1()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(true);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value1',
                ],
                [
                    'type' => 'equals',
                    'attribute' => 'test3.test2',
                    'value' => 'value2',
                ],
                [
                    'type' => 'linkedWith',
                    'attribute' => 'test3',
                    'value' => 'value3',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->any())
            ->method('hasAttribute')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('getRelationParam')
            ->with('test3', 'entity')
            ->willReturn($this->foreignEntityType);

        $this->acl
            ->expects($this->any())
            ->method('getScopeForbiddenAttributeList')
            ->willReturnMap([
                [$this->entityType, Table::ACTION_READ, Table::LEVEL_NO, []],
                [$this->foreignEntityType, Table::ACTION_READ, Table::LEVEL_NO, []],
            ]);

        $this->acl
            ->expects($this->any())
            ->method('getScopeForbiddenFieldList')
            ->with($this->entityType)
            ->willReturn([]);

        $this->acl
            ->expects($this->any())
            ->method('getScopeForbiddenLinkList')
            ->with($this->entityType)
            ->willReturn([]);

        $this->acl
            ->expects($this->any())
            ->method('checkScope')
            ->with($this->foreignEntityType)
            ->willReturn(true);

        $foreign = $this->createMock(Entity::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getEntityById')
            ->willReturnMap([
                [$this->foreignEntityType, 'value3', $foreign]
            ]);

        $this->acl
            ->expects($this->any())
            ->method('checkEntityRead')
            ->willReturnMap([
                [$foreign, true]
            ]);

        $this->entity
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->entityHelper
            ->method('getRelationEntityType')
            ->willReturnMap([
                [$this->entity, 'test3', $this->foreignEntityType],
            ]);

        $this->checker->check($item, $this->params);

        $this->assertTrue(true);
    }

    public function testPermissions2()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(true);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test1',
                    'value' => 'value1',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->any())
            ->method('hasAttribute')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->acl
            ->expects($this->any())
            ->method('getScopeForbiddenAttributeList')
            ->with($this->entityType)
            ->willReturn(['test1']);

        $this->expectException(Forbidden::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testPermissions3()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(true);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'test3.test2',
                    'value' => 'value2',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->any())
            ->method('hasAttribute')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('getRelationParam')
            ->with('test3', 'entity')
            ->willReturn($this->foreignEntityType);

        $this->acl
            ->expects($this->any())
            ->method('checkScope')
            ->with($this->foreignEntityType)
            ->willReturn(false);

        $this->expectException(Forbidden::class);

        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testPermissions4()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(false);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(true);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'linkedWith',
                    'attribute' => 'test3',
                    'value' => 'value3',
                ],
            ],
        ]);

        $this->entity
            ->expects($this->any())
            ->method('hasAttribute')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('hasRelation')
            ->with('test3')
            ->willReturn(true);

        $this->entity
            ->expects($this->any())
            ->method('getRelationParam')
            ->with('test3', 'entity')
            ->willReturn($this->foreignEntityType);

        $this->acl
            ->expects($this->any())
            ->method('checkScope')
            ->with($this->foreignEntityType)
            ->willReturn(false);

        $this->expectException(Forbidden::class);

        $this->checker->check($item, $this->params);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testComplexExpressions1()
    {
        $this->params
            ->expects($this->any())
            ->method('forbidComplexExpressions')
            ->willReturn(true);

        $this->params
            ->expects($this->any())
            ->method('applyPermissionCheck')
            ->willReturn(false);

        $item = Item::fromRaw([
            'type' => 'and',
            'value' => [
                [
                    'type' => 'equals',
                    'attribute' => 'TEST:(test2)',
                    'value' => 'value2',
                ]
            ],
        ]);

        $this->expectException(Forbidden::class);

        $this->checker->check($item, $this->params);
    }
}
