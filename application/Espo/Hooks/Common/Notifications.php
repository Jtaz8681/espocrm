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

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Tools\Notification\HookProcessor;
use Espo\ORM\Entity;

class Notifications
{
    public static int $order = 10;

    private HookProcessor $processor;

    public function __construct(HookProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterSave(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::SILENT]) || !empty($options[SaveOption::NO_NOTIFICATIONS])) {
            return;
        }

        $this->processor->afterSave($entity, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function beforeRemove(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::SILENT]) || !empty($options[SaveOption::NO_NOTIFICATIONS])) {
            return;
        }

        $this->processor->beforeRemove($entity, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterRemove(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::SILENT])) {
            return;
        }

        $this->processor->afterRemove($entity);
    }
}
