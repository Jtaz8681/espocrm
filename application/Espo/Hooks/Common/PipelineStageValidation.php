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

namespace Espo\Hooks\Common;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Name\Field;
use Espo\Entities\PipelineStage;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Exceptions\ValidationException;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Pipeline\MetadataProvider;

/**
 * @noinspection PhpUnused
 */
class PipelineStageValidation implements BeforeSave
{
    public function __construct(
        private MetadataProvider $metadataProvider,
        private EntityManager $entityManager,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$this->metadataProvider->isEnabled($entity->getEntityType())) {
            return;
        }

        if (
            !$entity->isAttributeChanged(Field::PIPELINE . 'Id') &&
            !$entity->isAttributeChanged(Field::PIPELINE_STAGE . 'Id')
        ) {
            return;
        }

        $pipelineId = $entity->get(Field::PIPELINE . 'Id');
        $stageId = $entity->get(Field::PIPELINE_STAGE . 'Id');

        if (!$stageId) {
            return;
        }

        $stage = $this->entityManager->getRDBRepositoryByClass(PipelineStage::class)->getById($stageId);

        if (!$stage) {
            throw new ValidationException("Pipeline stage '$stageId' does not exist.");
        }

        if ($stage->getPipeline()->getId() !== $pipelineId) {
            throw new ValidationException("Pipeline stage does not belong to the set pipelined.");
        }
    }
}
