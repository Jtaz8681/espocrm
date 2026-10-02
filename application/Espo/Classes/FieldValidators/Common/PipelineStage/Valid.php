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

namespace Espo\Classes\FieldValidators\Common\PipelineStage;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\Name\Field;
use Espo\Entities\PipelineStage;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements Validator<Entity>
 */
class Valid implements Validator
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $pipelineId = $entity->get(Field::PIPELINE . 'Id');
        $stageId = $entity->get(Field::PIPELINE_STAGE . 'Id');

        if (!$pipelineId && $stageId) {
            return Failure::create();
        }

        $stage = $this->entityManager->getRDBRepositoryByClass(PipelineStage::class)->getById($stageId);

        if (!$stage) {
            return Failure::create();
        }

        if ($stage->getPipeline()->getId() !== $pipelineId) {
            return Failure::create();
        }

        return null;
    }
}
