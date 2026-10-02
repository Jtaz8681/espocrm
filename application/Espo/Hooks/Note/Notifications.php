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

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Repository\Option\SaveContext;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Notification\HookProcessor\Params;
use Espo\Tools\Notification\NoteHookProcessor;
use Espo\Entities\Note;

/**
 * @implements AfterSave<Note>
 */
class Notifications implements AfterSave
{
    public static int $order = 14;

    public function __construct(private NoteHookProcessor $processor)
    {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew() && !$options->get('forceProcessNotifications')) {
            return;
        }

        $saveContext = SaveContext::obtainFromOptions($options);

        $params = new Params(actionId: $saveContext?->getActionId());

        $this->processor->afterSave($entity, $params);
    }
}
