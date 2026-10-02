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

namespace Espo\Modules\Crm\Hooks\Task;

use Espo\Core\Field\DateTime;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Entity;

class DateCompleted
{
    private const FIELD_DATE_COMPLETED = 'dateCompleted';
    private const FIELD_STATUS = 'status';

    public function __construct() {}

    /**
     * @param Task $entity
     * @param array<string, mixed> $options
     */
    public function beforeSave(Entity $entity, array $options): void
    {
        if (!$entity->isAttributeChanged(self::FIELD_STATUS)) {
            return;
        }

        if (
            ($options[SaveOption::IMPORT] ?? false) &&
            $entity->get(self::FIELD_DATE_COMPLETED)
        ) {
            return;
        }

        if ($entity->getStatus() !== Task::STATUS_COMPLETED) {
            $entity->set(self::FIELD_DATE_COMPLETED, null);

            return;
        }

        $entity->setValueObject(self::FIELD_DATE_COMPLETED, DateTime::createNow());
    }
}
