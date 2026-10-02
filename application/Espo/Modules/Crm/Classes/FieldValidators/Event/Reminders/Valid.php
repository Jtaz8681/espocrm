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
use Espo\Modules\Crm\Entities\Reminder;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use stdClass;

/**
 * @implements Validator<Entity>
 */
class Valid implements Validator
{
    public function __construct(
        private Defs $ormDefs
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        /** @var ?mixed[] $list */
        $list = $entity->get($field);

        if ($list === null) {
            return null;
        }

        $typeList = $this->ormDefs
            ->getEntity(Reminder::ENTITY_TYPE)
            ->getField('type')
            ->getParam('options') ?? [];

        foreach ($list as $item) {
            if (!$item instanceof stdClass) {
                return Failure::create();
            }

            $seconds = $item->seconds ?? null;
            $type = $item->type ?? null;

            if (!is_int($seconds)) {
                return Failure::create();
            }

            if ($seconds < 0) {
                return Failure::create();
            }

            if (!in_array($type, $typeList)) {
                return Failure::create();
            }
        }

        return null;
    }
}
