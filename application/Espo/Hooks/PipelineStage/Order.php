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

namespace Espo\Hooks\PipelineStage;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\Tools\Pipeline\MoveService;
use Espo\ORM\Collection;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\OpenApi\Util\EnumOptionsProvider;

/**
 * @implements BeforeSave<PipelineStage>
 * @implements AfterRemove<PipelineStage>
 */
class Order implements BeforeSave, AfterRemove
{
    public function __construct(
        private EntityManager $entityManager,
        private MoveService $moveService,
        private Defs $defs,
        private EnumOptionsProvider $enumOptionsProvider,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $stages = $this->getStages($entity);

        $i = 0;
        $newList = [];

        foreach ($this->getStatusList($entity) as $status) {
            foreach ($stages as $stage) {
                if ($stage->getMappedStatus() === $status) {
                    $newList[] = $stage;

                    $stage->setOrder($i);
                    $i++;
                }
            }

            if ($entity->getMappedStatus() === $status) {
                $newList[] = $entity;

                $entity->setOrder($i);
                $i++;
            }
        }

        foreach ($newList as $stage) {
            if ($stage->getId() === $entity->getId()) {
                continue;
            }

            $this->entityManager->saveEntity($stage, [SaveOption::SILENT => true]);
        }
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $this->moveService->reOrder($entity::class, $entity->get(PipelineStage::ATTR_PIPELINE_ID));
    }

    /**
     * @return iterable<PipelineStage>
     */
    private function getStages(PipelineStage $entity): iterable
    {
        /** @var Collection<PipelineStage> */
        return $this->entityManager
            ->getRelation($entity->getPipeline(), Pipeline::LINK_STAGES)
            ->order(PipelineStage::FIELD_ORDER)
            ->find();
    }

    /**
     * @return string[]
     */
    private function getStatusList(PipelineStage $entity): array
    {
        $targetEntityType = $entity->getPipeline()->getTargetEntityType();
        $field = $entity->getPipeline()->getTargetField();

        $fieldDefs = $this->defs
            ->getEntity($targetEntityType)
            ->getField($field);

        return $this->enumOptionsProvider->get($fieldDefs) ?? [];
    }
}
