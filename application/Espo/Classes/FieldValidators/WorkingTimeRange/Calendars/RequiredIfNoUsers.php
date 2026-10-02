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

namespace Espo\Classes\FieldValidators\WorkingTimeRange\Calendars;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Entities\WorkingTimeRange;
use Espo\ORM\Entity;

/**
 * @implements Validator<WorkingTimeRange>
 */
class RequiredIfNoUsers implements Validator
{

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        if ($entity->getCalendars()->getCount() !== 0 || $entity->getUsers()->getCount() !== 0) {
            return null;
        }

        return Failure::create();
    }
}
