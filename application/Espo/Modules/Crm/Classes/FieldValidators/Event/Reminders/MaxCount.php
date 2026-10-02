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

namespace Espo\Modules\Crm\Classes\FieldValidators\Event\Reminders;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

/**
 * @implements Validator<Entity>
 */
class MaxCount implements Validator
{
    private const MAX_COUNT = 10;

    public function __construct(private Config $config)
    {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $maxCount = $this->config->get('reminderMaxCount') ?? self::MAX_COUNT;

        $value = $entity->get($field);

        if (!is_array($value)) {
            return null;
        }

        if (count($value) <= $maxCount) {
            return null;
        }

        return Failure::create();
    }
}
