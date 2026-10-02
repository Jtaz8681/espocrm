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

namespace Espo\Classes\FieldValidators\PipelineStatus\MappedStatus;

use Espo\Core\Utils\Metadata;
use Espo\Entities\PipelineStage;
use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\Tools\OpenApi\Util\EnumOptionsProvider;

/**
 * @implements Validator<PipelineStage>
 */
class Valid implements Validator
{
    public function __construct(
        private Metadata $metadata,
        private EnumOptionsProvider $enumOptionsProvider,
        private Defs $defs,
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $status = $entity->getMappedStatus();

        $entityType = $entity->getPipeline()->getTargetEntityType();

        $field = $this->metadata->get("scopes.$entityType.statusField");

        if (!$field) {
            return null;
        }

        $fieldDefs = $this->defs
            ->getEntity($entityType)
            ->getField($field);

        $options = $this->enumOptionsProvider->get($fieldDefs);

        if (!$options) {
            return Failure::create();
        }

        if (!in_array($status, $options)) {
            return Failure::create();
        }

        return null;
    }
}
