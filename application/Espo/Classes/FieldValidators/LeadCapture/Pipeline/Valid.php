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

namespace Espo\Classes\FieldValidators\LeadCapture\Pipeline;

use Espo\Entities\LeadCapture;
use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;

/**
 * @implements Validator<LeadCapture>
 */
class Valid implements Validator
{
    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $pipeline = $entity->getPipeline();

        if (!$pipeline) {
            return null;
        }

        if ($pipeline->getTargetEntityType() !== Lead::ENTITY_TYPE) {
            return Failure::create();
        }

        return null;
    }
}
