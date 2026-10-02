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

namespace tests\unit\Espo\Core\Job\QueueProcessor;

use Countable;
use Espo\Core\Job\QueueProcessor\Params;
use Espo\Core\Job\QueueProcessor\Picker;
use Espo\Core\Job\QueueUtil;
use Espo\Entities\Job;
use Espo\ORM\Collection;
use Espo\ORM\EntityCollection;

use PHPUnit\Framework\TestCase;

class PickerTest extends TestCase
{
    /**
     * @param string[] $ids
     * @return Collection<Job>&Countable
     */
    private function createCollection(array $ids): Collection&Countable
    {
        $output = new EntityCollection();

        foreach ($ids as $id) {
            $job = $this->createMock(Job::class);

            $job->expects($this->any())
                ->method('getId')
                ->willReturn($id);

            $output[] = $job;
        }

        return $output;
    }

    /**
     * @param string[] $ids
     * @param iterable<Job> $pick
     */
    private function assertPick(array $ids, iterable $pick): void
    {
        $pick = iterator_to_array($pick);

        $this->assertSameSize($ids, $pick);

        foreach ($pick as $i => $job) {
            $this->assertEquals($ids[$i], $job->getId());
        }
    }

    /**
     * @param float[] $weights
     * @param string[][] $idsGroups
     * @param string[] $idsExpected
     */
    private function prepareAndTest(int $limit, array $weights, array $idsGroups, array $idsExpected): void
    {
        $util = $this->createMock(QueueUtil::class);

        $params = Params::create()->withLimit($limit);

        $paramList = [];

        foreach ($weights as $i => $weight) {
            $paramList[] = $params
                ->withWeight($weight)
                ->withQueue('m' . $i);
        }


        $params = $params->withSubQueueParamsList($paramList);

        $picker = new Picker($util);

        $collections = [];

        foreach ($idsGroups as $ids) {
            $collections[] = $this->createCollection($ids);
        }

        $util->expects($this->any())
            ->method('getPendingJobs')
            ->willReturnOnConsecutiveCalls(...$collections);

        $pick = $picker->pick($params);

        $this->assertPick($idsExpected, $pick);
    }

    public function testPick0(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [1.0],
            idsGroups: [
                ['a0', 'a1', 'a2', 'a3', 'a4', 'a5'],
            ],
            idsExpected:
                ['a0', 'a1', 'a2', 'a3', 'a4', 'a5'],
        );
    }

    public function testPick1(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2', 'a3', 'a4', 'a5'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5'],
            ],
            idsExpected: array_merge(
                ['a0', 'a1', 'a2', 'a3', 'a4'],
                ['b0', 'b1', 'b2', 'b3', 'b4'],
            ),
        );
    }

    public function testPick2(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7'],
            ],
            idsExpected: array_merge(
                ['a0', 'a1', 'a2'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6'],
            ),
        );
    }

    public function testPick3(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2', 'a4', 'a5', 'a6', 'a7'],
                ['b0', 'b1', 'b2'],
            ],
            idsExpected:
                ['a0', 'a1', 'a2', 'a4', 'a5', 'b0', 'b1', 'b2', 'a6', 'a7'],
        );
    }

    public function testPick4(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2'],
                ['b0', 'b1', 'b2'],
            ],
            idsExpected:
            ['a0', 'a1', 'a2', 'b0', 'b1', 'b2'],
        );
    }

    public function testPick5(): void
    {
        $this->prepareAndTest(
            limit: 15,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'b11', 'b12'],
            ],
            idsExpected: array_merge(
                ['a0', 'a1', 'a2'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'b11'],
            ),
        );
    }

    public function testPick6(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                [],
                ['b0', 'b1', 'b2'],
            ],
            idsExpected:
            [ 'b0', 'b1', 'b2'],
        );
    }

    public function testPick7(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.5],
            idsGroups: [
                ['a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5'],
            ],
            idsExpected: array_merge(
                ['a0', 'a1', 'a2', 'a3', 'a4'],
                ['b0', 'b1', 'b2', 'b3', 'b4'],
            ),
        );
    }

    public function testPick8(): void
    {
        $this->prepareAndTest(
            limit: 10,
            weights: [0.5, 0.3, 0.2],
            idsGroups: [
                ['a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6'],
                ['b0', 'b1', 'b2', 'b3', 'b4', 'b5'],
                ['c0', 'c1', 'c2', 'c3', 'c4', 'c5'],
            ],
            idsExpected: array_merge(
                ['a0', 'a1', 'a2', 'a3', 'a4'],
                ['b0', 'b1', 'b2'],
                ['c0', 'c1'],
            ),
        );
    }
}
