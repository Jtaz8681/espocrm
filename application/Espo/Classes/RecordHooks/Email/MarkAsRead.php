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

namespace Espo\Classes\RecordHooks\Email;

use Espo\Core\Record\Hook\ReadHook;
use Espo\Core\Record\ReadParams;
use Espo\Entities\Email;
use Espo\ORM\Entity;
use Espo\Tools\Email\InboxService;

/**
 * @implements ReadHook<Email>
 */
class MarkAsRead implements ReadHook
{
    public function __construct(
        private InboxService $inboxService
    ) {}

    public function process(Entity $entity, ReadParams $params): void
    {
        if ($entity->isRead()) {
            return;
        }

        $this->inboxService->markAsRead($entity->getId());
    }
}
