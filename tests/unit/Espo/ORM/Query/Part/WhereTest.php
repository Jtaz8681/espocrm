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

namespace tests\unit\Espo\ORM\Query\Part;

use Espo\ORM\{
    Query\Part\Where\AndGroup,
    Query\Part\Where\OrGroup,
    Query\Part\WhereClause,
    Query\Part\Where\Comparison as Comp,
    Query\Part\Expression as Expr,
    Query\Part\Condition as Cond,
    Query\SelectBuilder,
};

class WhereTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
    }

    public function testAndGroup1(): void
    {
        $raw =
            AndGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->build()
                ->getRaw();


        $expextedRaw = [
            'AND' => [
                'test1' => '1',
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAndGroup2(): void
    {
        $raw =
            AndGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                        'test1a' => '1a',
                    ])
                )
                ->add(
                    WhereClause::fromRaw([
                        'test2' => '2',
                    ])
                )
                ->build()
                ->getRaw();


        $expextedRaw = [
            'AND' => [
                ['test1' => '1', 'test1a' => '1a'],
                ['test2' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAndGroup3(): void
    {
        $raw =
            AndGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->add(
                    OrGroup::createBuilder()
                        ->add(
                            WhereClause::fromRaw([
                                'o1' => '1',
                            ])
                        )
                        ->add(
                            WhereClause::fromRaw([
                                'o2' => '2',
                            ])
                        )
                        ->build()
                )
                ->build()
                ->getRaw();


        $expextedRaw = [
            'AND' => [
                ['test1' => '1'],
                [
                    'OR' => [
                        ['o1' => '1'],
                        ['o2' => '2'],
                    ],
                ],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testOrGroup1(): void
    {
        $raw =
            OrGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->build()
                ->getRaw();

        $expextedRaw = [
            'OR' => [
                ['test1' => '1'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testOrGroup2(): void
    {
        $raw =
            OrGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                        'test2' => '2',
                    ])
                )
                ->build()
                ->getRaw();


        $expextedRaw = [
            'OR' => [
                [
                    'test1' => '1',
                    'test2' => '2',
                ],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAndGroupMerge1(): void
    {
        $group1 =
            AndGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->build();

        $group =
            AndGroup::createBuilder()
                ->merge($group1)
                ->add(
                    WhereClause::fromRaw([
                        'test2' => '2',
                    ])
                )
                ->build();

        $raw = $group->getRaw();

        $expextedRaw = [
            'AND' => [
                ['test1' => '1'],
                ['test2' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testOrGroupMerge1(): void
    {
        $orGroup1 =
            OrGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->build();

        $orGroup =
            OrGroup::createBuilder()
                ->merge($orGroup1)
                ->add(
                    WhereClause::fromRaw([
                        'test2' => '2',
                    ])
                )
                ->build();

        $raw = $orGroup->getRaw();

        $expextedRaw = [
            'OR' => [
                ['test1' => '1'],
                ['test2' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testOrGroupMerge2(): void
    {
        $orGroup1 =
            OrGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test1' => '1',
                    ])
                )
                ->build();

        $orGroup =
            OrGroup::createBuilder()
                ->add(
                    WhereClause::fromRaw([
                        'test2' => '2',
                    ])
                )
                ->merge($orGroup1)
                ->build();

        $raw = $orGroup->getRaw();

        $expextedRaw = [
            'OR' => [
                ['test2' => '2'],
                ['test1' => '1'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAndGroupCreate1(): void
    {
        $raw = AndGroup
            ::create(
                WhereClause::fromRaw(['test1' => '1']),
                WhereClause::fromRaw(['test2' => '2'])
            )
            ->getRaw();

        $expextedRaw = [
            'AND' => [
                ['test1' => '1'],
                ['test2' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testOrGroupCreate1(): void
    {
        $raw = OrGroup
            ::create(
                WhereClause::fromRaw(['test1' => '1']),
                WhereClause::fromRaw(['test2' => '2'])
            )
            ->getRaw();

        $expextedRaw = [
            'OR' => [
                ['test1' => '1'],
                ['test2' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAndGroupCreateWithCmp(): void
    {
        $raw = AndGroup
            ::create(
                Comp::equal(Expr::column('test1'), '1'),
                Comp::equal(Expr::column('test2'), '2')
            )
            ->getRaw();

        $expextedRaw = [
            'AND' => [
                ['test1=' => '1'],
                ['test2=' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAll(): void
    {
        $raw =
            Cond::and(
                Cond::equal(Cond::column('test1'), '1'),
                Cond::notEqual(Expr::column('test2'), '2'),
                Cond::greater(Expr::column('test3'), 3),
                Expr::greater(Expr::column('test4'), 4),
                Cond::equal(Expr::column('test5'), Expr::column('hello5')),
                Comp::equal(Expr::column('test6'), Expr::column('hello6')),
                Cond::equal(Expr::column('test9'), Expr::column('hello9')),
                Comp::greaterOrEqual(Expr::column('test10'), Expr::column('hello10'))
            )
            ->getRaw();

        $expextedRaw = [
            'AND' => [
                ['test1=' => '1'],
                ['test2!=' => '2'],
                ['test3>' => 3],
                ['GREATER_THAN:(test4, 4):' => null],
                ['test5=:' => 'hello5'],
                ['test6=:' => 'hello6'],
                ['test9=:' => 'hello9'],
                ['test10>=:' => 'hello10'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testAny(): void
    {
        $raw =
            Cond::or(
                Cond::equal(Expr::column('test1'), '1'),
                Cond::equal(Expr::column('test2'), '2')
            )
            ->getRaw();

        $expextedRaw = [
            'OR' => [
                ['test1=' => '1'],
                ['test2=' => '2'],
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testNot1(): void
    {
        $raw =
            Cond::not(
                Cond::or(
                    Cond::equal(Expr::column('test1'), '1'),
                    Cond::equal(Expr::column('test2'), '2')
                )
            )
            ->getRaw();

        $expextedRaw = [
            'NOT' => [
                'OR' => [
                    ['test1=' => '1'],
                    ['test2=' => '2'],
                ]
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testNot2(): void
    {
        $raw =
            Cond::not(
                Cond::equal(Expr::column('test1'), '1')
            )
            ->getRaw();

        $expextedRaw = [
            'NOT' => [
                'test1=' => '1',
            ]
        ];

        $this->assertEquals($expextedRaw, $raw);
    }

    public function testInQuery(): void
    {
        $query = (new SelectBuilder)->from('Test')->build();

        $raw =
            Cond::in(Expr::column('id'), $query)
            ->getRaw();

        $expectedRaw = [
            'id=s' => $query
        ];

        $this->assertEquals($expectedRaw, $raw);
    }

    public function testNotInQuery(): void
    {
        $query = (new SelectBuilder)->from('Test')->build();

        $raw =
            Cond::notIn(Expr::column('id'), $query)
            ->getRaw();

        $expectedRaw = [
            'id!=s' => $query
        ];

        $this->assertEquals($expectedRaw, $raw);
    }

    public function testInList(): void
    {
        $raw =
            Cond::in(Expr::column('status'), ['1', '2'])
            ->getRaw();

        $expectedRaw = [
            'status=' => ['1', '2']
        ];

        $this->assertEquals($expectedRaw, $raw);
    }

    public function testNotInList(): void
    {
        $raw =
            Cond::notIn(Expr::column('status'), ['1', '2'])
            ->getRaw();

        $expectedRaw = [
            'status!=' => ['1', '2']
        ];

        $this->assertEquals($expectedRaw, $raw);
    }
}
