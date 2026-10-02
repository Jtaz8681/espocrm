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

namespace Espo\Core\FieldValidation;

use Espo\ORM\Entity;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;

/**
 * A field validator. Checks whether field values are valid. Raw payload values are available in the `data`.
 * Values that managed to be set to the Entity can be obtained from the `entity`.
 *
 * Note: Validators are not supposed to perform any access control checks.
 *
 * @template TEntity of Entity
 */
interface Validator
{
    /**
     * @param TEntity $entity
     */
    public function validate(Entity $entity, string $field, Data $data): ?Failure;
}
