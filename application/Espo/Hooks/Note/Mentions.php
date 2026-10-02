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

namespace Espo\Hooks\Note;

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Tools\Notification\NoteMentionHookProcessor;
use Espo\ORM\Entity;
use Espo\Entities\Note;

class Mentions
{
    public static int $order = 9;

    private NoteMentionHookProcessor $processor;

    public function __construct(NoteMentionHookProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function beforeSave(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::SILENT])) {
            return;
        }

        assert($entity instanceof Note);

        $this->processor->beforeSave($entity);
    }
}
