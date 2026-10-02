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

namespace tests\unit\Espo\Tools\MassUpdate;

use Espo\Tools\MassUpdate\Data;
use Espo\Tools\MassUpdate\ValueMapPreparator;
use Espo\Tools\MassUpdate\Action;

use Espo\ORM\Entity;
use Espo\ORM\Defs as OrmDefs;
use PHPUnit\Framework\TestCase;

class ValueMapPreparatorTest extends TestCase
{
    public function testPrepare1(): void
    {
        $preparator = new ValueMapPreparator(
            $this->createMock(OrmDefs::class)
        );

        $data = Data::create()
            ->with('a1', ['1', '2'], Action::ADD)
            ->with('a2', ['1', '2', '3'], Action::REMOVE)
            ->with('a3', ['1', '2'], Action::UPDATE)
            ->with('a4', ['1'], Action::REMOVE)
            ->with('a5', [], Action::ADD)
            ->with('a6', [], Action::REMOVE)
            ->with('a7', ['1'], Action::ADD)
            ->with('a8', ['1'], Action::REMOVE)
            ->with('a9', null, Action::REMOVE)
            ->with('a10', null, Action::ADD)
            ->with('a11', null, Action::UPDATE)
            ->with('a12', (object) ['k2' => 'v2'], Action::ADD)
            ->with('a13', (object) ['k2' => 'v2'], Action::REMOVE);

        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $entity
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                    function ($attribute) {
                        $map = [
                            'a1' => ['0'],
                            'a2' => ['0', '1', '2'],
                            'a3' => ['0'],
                            'a4' => ['1'],
                            'a5' => ['1'],
                            'a6' => ['1'],
                            'a7' => null,
                            'a8' => null,
                            'a12' => (object) ['k1' => 'v1'],
                            'a13' => (object) ['k1' => 'v1', 'k2' => 'v2'],
                        ];

                        return $map[$attribute] ?? null;
                    }
            );

        $values = $preparator->prepare($entity, $data);

        $this->assertEquals(['0', '1', '2'], $values->a1);
        $this->assertEquals(['0'], $values->a2);
        $this->assertEquals(['1', '2'], $values->a3);
        $this->assertEquals([], $values->a4);
        $this->assertEquals(['1'], $values->a5);
        $this->assertEquals(['1'], $values->a6);
        $this->assertEquals(['1'], $values->a7);
        $this->assertEquals([], $values->a8);
        $this->assertFalse(property_exists($values, 'a9'));
        $this->assertFalse(property_exists($values, 'a10'));
        $this->assertEquals(null, $values->a11);
        $this->assertEquals((object) ['k1' => 'v1', 'k2' => 'v2'], $values->a12);
        $this->assertEquals((object) ['k1' => 'v1'], $values->a13);
    }
}
