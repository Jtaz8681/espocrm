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

namespace Espo\Tools\Pipeline;

use Espo\Core\Exceptions\Conflict;
use Espo\Entities\PipelineStage;
use Espo\ORM\EntityManager;
use RuntimeException;

class StageMoveService
{
    public function __construct(
        private EntityManager $entityManager,
        private CacheClearer $cacheClearer,
    ) {}

    /**
     * @throws Conflict
     */
    public function moveUp(PipelineStage $stage): void
    {
        $columns = $this->getStages($stage);
        $index = $this->getIndex($columns, $stage);

        $anotherColumn = $columns[$index - 1] ?? null;

        if (!$anotherColumn) {
            throw new Conflict("Can't move first.");
        }

        $this->swapStages($anotherColumn, $stage);

        $this->cacheClearer->clear();
    }

    /**
     * @throws Conflict
     */
    public function moveDown(PipelineStage $column): void
    {
        $columns = $this->getStages($column);
        $index = $this->getIndex($columns, $column);

        $anotherColumn = $columns[$index + 1] ?? null;

        if (!$anotherColumn) {
            throw new Conflict("Can't move last.");
        }

        $this->swapStages($anotherColumn, $column);
    }

    /**
     * @throws Conflict
     */
    private function swapStages(PipelineStage $anotherColumn, PipelineStage $column): void
    {
        if ($anotherColumn->getMappedStatus() !== $column->getMappedStatus()) {
            throw new Conflict("Can't break status order.");
        }

        $order = $column->getOrder();
        $column->setOrder($anotherColumn->getOrder());
        $anotherColumn->setOrder($order);

        $this->entityManager->saveEntity($column);
        $this->entityManager->saveEntity($anotherColumn);
    }

    /**
     * @return PipelineStage[]
     */
    private function getStages(PipelineStage $stage): array
    {
        $collection = $this->entityManager
            ->getRDBRepositoryByClass(PipelineStage::class)
            ->where([PipelineStage::ATTR_PIPELINE_ID => $stage->getPipeline()->getId()])
            ->order(PipelineStage::FIELD_ORDER)
            ->find();

        return iterator_to_array($collection);
    }

    /**
     * @param PipelineStage[] $columns
     */
    private function getIndex(array $columns, PipelineStage $column): int
    {
        $index = -1;

        foreach ($columns as $i => $it) {
            if ($it->getId() === $column->getId()) {
                $index = $i;

                break;
            }
        }

        if ($index < 0) {
            throw new RuntimeException("Stage not found.");
        }

        return $index;
    }
}
