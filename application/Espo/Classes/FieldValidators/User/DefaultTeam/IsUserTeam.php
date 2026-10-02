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

namespace Espo\Classes\FieldValidators\User\DefaultTeam;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements Validator<User>
 */
class IsUserTeam implements Validator
{
    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        if (!$entity->getDefaultTeam()) {
            return null;
        }

        if (in_array($entity->getDefaultTeam()->getId(), $entity->getTeamIdList())) {
            return null;
        }

        return Failure::create();
    }
}
