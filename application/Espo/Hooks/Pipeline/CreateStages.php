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

namespace Espo\Hooks\Pipeline;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Language;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\OpenApi\Util\EnumOptionsProvider;

/**
 * @implements AfterSave<Pipeline>
 */
class CreateStages implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager,
        private Language $defaultLanguage,
        private Defs $defs,
        private EnumOptionsProvider $enumOptionsProvider,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $targetEntityType = $entity->getTargetEntityType();
        $field = $entity->getTargetField();

        if (
            !$entity->isNew() ||
            !$options->get(SaveOption::API) && !$options->get('createStages')
        ) {
            return;
        }

        if ($options->get(SaveOption::DUPLICATE_SOURCE_ID)) {
            $this->processDuplicate($entity, $options->get(SaveOption::DUPLICATE_SOURCE_ID));

            return;
        }

        $fieldDefs = $this->defs
            ->getEntity($targetEntityType)
            ->getField($field);

        $options = $this->enumOptionsProvider->get($fieldDefs) ?? [];

        foreach ($options as $i => $option) {
            $column = $this->entityManager->getRDBRepositoryByClass(PipelineStage::class)->getNew();

            $name = $this->defaultLanguage->translateOption($option, $field, $targetEntityType);

            $column
                ->setName($name)
                ->setMappedStatus($option)
                ->setPipeline($entity)
                ->setOrder($i);

            $this->entityManager->saveEntity($column);
        }
    }

    private function processDuplicate(Pipeline $entity, string $sourceId): void
    {
        $source = $this->entityManager->getRDBRepositoryByClass(Pipeline::class)->getById($sourceId);

        if (!$source) {
            return;
        }

        /** @var iterable<PipelineStage> $sourceStages */
        $sourceStages = $this->entityManager
            ->getRelation($source, Pipeline::LINK_STAGES)
            ->order(PipelineStage::FIELD_ORDER)
            ->find();

        foreach ($sourceStages as $i => $sourceStage) {
            $stage = $this->entityManager->getRDBRepositoryByClass(PipelineStage::class)->getNew();

            $stage
                ->setName($sourceStage->getName())
                ->setMappedStatus($sourceStage->getMappedStatus())
                ->setOrder($i)
                ->setPipeline($entity);

            $this->entityManager->saveEntity($stage);
        }
    }
}
