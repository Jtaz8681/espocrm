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

use Espo\Core\MassAction\Data as MassActionData;
use Espo\Tools\MassUpdate\Data;
use Espo\Tools\MassUpdate\Action;

class DataTest extends \PHPUnit\Framework\TestCase
{
    public function testData1(): void
    {
        $massActionData = MassActionData::fromRaw(
            (object) [
                'values' => (object) [
                    'a1' => '1',
                    'a2' => '2',
                    'a3' => '3',
                ],
                'actions' => (object) [
                    'a1' => 'update',
                    'a2' => 'add',
                    'a3' => 'remove',
                ],
            ],
        );

        $data = Data::fromMassActionData($massActionData);

        $this->assertEquals('1', $data->getValue('a1'));
        $this->assertEquals(null, $data->getValue('a0'));
        $this->assertEquals(true, $data->has('a1'));
        $this->assertEquals(false, $data->has('a0'));

        $this->assertEquals(['a1', 'a2', 'a3'], $data->getAttributeList());
        $this->assertEquals(
            (object) [
                    'a1' => '1',
                    'a2' => '2',
                    'a3' => '3',
                ],
            $data->getValues()
        );

        $this->assertEquals(Action::UPDATE, $data->getAction('a1'));
        $this->assertEquals(Action::ADD, $data->getAction('a2'));
        $this->assertEquals(Action::REMOVE, $data->getAction('a3'));

        $this->assertEquals('1m', $data->with('a1', '1m')->getValue('a1'));
        $this->assertEquals(false, $data->without('a1')->has('a1'));

        $massActionDataModified = $data
            ->with('a1', '1m', Action::ADD)
            ->without('a2')
            ->toMassActionData();

        $values = $massActionDataModified->get('values');
        $actions = $massActionDataModified->get('actions');

        $this->assertEquals('1m', $values->a1);
        $this->assertFalse(property_exists($values, 'a2'));

        $this->assertEquals(Action::ADD, $actions->a1);
        $this->assertFalse(property_exists($actions, 'a2'));
    }

    public function testCreate(): void
    {
        $data = Data::create()
            ->with('a1', null, Action::UPDATE)
            ->with('a2', ['1'], Action::ADD);

        $this->assertEquals(null, $data->getValue('a1'));
        $this->assertEquals(['1'], $data->getValue('a2'));
        $this->assertEquals(Action::ADD, $data->getAction('a2'));
    }

    public function testWith(): void
    {
        $data = Data::create()
            ->with('a1', '1', Action::ADD)
            ->with('a1', '2')
            ->with('a2', '2');

        $this->assertEquals(Action::ADD, $data->getAction('a1'));
        $this->assertEquals(Action::UPDATE, $data->getAction('a2'));
    }

    public function testBc(): void
    {
        $massActionData = MassActionData::fromRaw(
            (object) [
                'a1' => '1',
                'a2' => '2',
                'a3' => '3',
            ],
        );

        $data = Data::fromMassActionData($massActionData);

        $this->assertEquals('1', $data->getValue('a1'));
        $this->assertEquals(Action::UPDATE, $data->getAction('a1'));
    }
}
