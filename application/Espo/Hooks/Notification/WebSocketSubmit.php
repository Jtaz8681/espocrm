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

namespace Espo\Hooks\Notification;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Entities\Notification;
use Espo\ORM\Entity;
use Espo\Core\WebSocket\Submission as WebSocketSubmission;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Notification>
 */
class WebSocketSubmit implements AfterSave
{
    public static int $order = 20;

    public function __construct(
        private WebSocketSubmission $webSocketSubmission,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $userId = $entity->getUserId();

        if (!$userId) {
            return;
        }

        $this->webSocketSubmission->submit('newNotification', $userId);
    }
}
