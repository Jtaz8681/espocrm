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

use Espo\ORM\Query\Delete;
use Espo\ORM\Query\Insert;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\Union;
use Espo\ORM\Query\Update;
use Espo\ORM\QueryBuilder;

use RuntimeException;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    protected function setUp() : void
    {
        $this->queryBuilder = new QueryBuilder();
    }

    public function testClone1()
    {
        $select = $this->queryBuilder
            ->select()
            ->from('Test')
            ->build();

        $clonedSelect = $this->queryBuilder
            ->clone($select)
            ->distinct()
            ->build();

        $this->assertNotSame($clonedSelect, $select);
        $this->assertInstanceOf(Select::class, $clonedSelect);
    }

    public function testSelectNoFrom1()
    {
        $select = $this->queryBuilder
            ->select()
            ->select(['col1'])
            ->build();

        $this->assertNull($select->getFrom());
    }

    public function testSelectNoFrom2()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->select()
            ->select(['col1'])
            ->join('test')
            ->build();
    }

    public function testSelectFrom1()
    {
        $select = $this->queryBuilder
            ->select(['col1', 'col2'])
            ->from('Test')
            ->build();

        $this->assertEquals(
            [
                Selection::fromString('col1'),
                Selection::fromString('col2'),
            ],
            $select->getSelect()
        );
    }

    public function testSelectFrom2()
    {
        $select = $this->queryBuilder
            ->select('col1', 'alias1')
            ->from('Test')
            ->build();

        $this->assertEquals(
            [
                Selection::fromString('col1')->withAlias('alias1')
            ],
            $select->getSelect()
        );
    }
    public function testInsert1()
    {
        $insert = $this->queryBuilder
            ->insert()
            ->into('Test')
            ->columns(['col1'])
            ->values(['col1' => '1'])
            ->build();

        $this->assertInstanceOf(Insert::class, $insert);
    }

    public function testInsert2()
    {
        $select = $this->queryBuilder
            ->select()
            ->from('Test')
            ->build();

        $insert = $this->queryBuilder
            ->insert()
            ->into('Test')
            ->columns(['col1'])
            ->valuesQuery($select)
            ->build();

        $this->assertInstanceOf(Insert::class, $insert);
    }

    public function testInsertNoInto()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->insert()
            ->columns(['col1'])
            ->values(['col1' => '1'])
            ->build();
    }

    public function testDelete1()
    {
        $delete = $this->queryBuilder
            ->delete()
            ->from('Test')
            ->where(['col1' => '1'])
            ->limit(1)
            ->build();

        $this->assertInstanceOf(Delete::class, $delete);
    }

    public function testDeleteNoFrom()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->delete()
            ->where(['col1' => '1'])
            ->build();
    }

    public function testUpdateNoIn()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->update()
            ->set(['col1' => '2'])
            ->where(['col1' => '1'])
            ->build();
    }

    public function testUpdate1()
    {
        $update = $this->queryBuilder
            ->update()
            ->in('Test')
            ->set(['col1' => '2'])
            ->where(['col1' => '1'])
            ->limit(1)
            ->build();

        $this->assertInstanceOf(Update::class, $update);
    }

    public function testUpdateNoSet()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->update()
            ->in('Test')
            ->where(['col1' => '1'])
            ->limit(1)
            ->build();
    }

    public function testUpdate2()
    {
        $update = $this->queryBuilder
            ->update()
            ->in('Test')
            ->set(['col' => Expression::add(1, 2)])
            ->build();

        $this->assertInstanceOf(Update::class, $update);

        $this->assertEquals(
            ['col:' => 'ADD:(1, 2)'],
            $update->getRaw()['set']
        );

        $set = $update->getSet();

        $this->assertEquals('ADD:(1, 2)', $set['col']->getValue());
    }

    public function testUnion1()
    {
        $q1 = $this->queryBuilder
            ->select()
            ->select(['col1'])
            ->build();

        $q2 = $this->queryBuilder
            ->select()
            ->select(['col1'])
            ->build();

        $union = $this->queryBuilder
            ->union()
            ->query($q1)
            ->query($q2)
            ->all()
            ->limit(0, 5)
            ->order(1, 'DESC')
            ->build();

        $this->assertInstanceOf(Union::class, $union);
    }

    public function testUnionNoQuery()
    {
        $this->expectException(RuntimeException::class);

        $this->queryBuilder
            ->union()
            ->build();
    }
}
