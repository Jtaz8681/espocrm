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

namespace Espo\Modules\Crm\Hooks\Common;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Name\Field;
use Espo\Core\Templates\Entities\Event;
use Espo\Core\WebSocket\Submission;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Entity>
 * @implements AfterRemove<Entity>
 */
class CalendarWebSocket implements AfterSave, AfterRemove
{
    public function __construct(
        private Submission $submission,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity instanceof Event) {
            return;
        }

        $this->process($entity);
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        if (!$entity instanceof Event) {
            return;
        }

        $this->process($entity);
    }

    private function process(Event $entity): void
    {
        if ($entity->hasLinkMultipleField(Field::ASSIGNED_USERS)) {
            foreach ($entity->getLinkMultipleIdList(Field::ASSIGNED_USERS) as $userId) {
                $this->submission->submit('calendarUpdate', $userId);
            }

            return;
        }

        if ($entity->getAssignedUser()) {
            $this->submission->submit('calendarUpdate', $entity->getAssignedUser()->getId());
        }
    }
}
