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

namespace Espo\Classes\RecordHooks\Webhook;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Webhook\Manager;
use Espo\Entities\Webhook;
use Espo\ORM\Entity;
use RuntimeException;

/**
 * @implements SaveHook<Webhook>
 */
class AfterSave implements SaveHook
{
    public function __construct(
        private Manager $webhookManager
    ) {}

    public function process(Entity $entity): void
    {
        $event = $entity->getEvent();

        if (!$event) {
            throw new RuntimeException("No 'event'.");
        }

        if ($entity->isNew()) {
            if ($entity->isActive()) {
                $this->webhookManager->addEvent($event);
            }

            return;
        }

        if (!$entity->isAttributeChanged('isActive')) {
            return;
        }

        if ($entity->isActive()) {
            $this->webhookManager->addEvent($event);

            return;
        }

        $this->webhookManager->removeEvent($event);
    }
}
