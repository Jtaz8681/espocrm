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

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Webhook\Options;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Metadata;
use Espo\Core\Webhook\Manager as WebhookManager;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Entity>
 * @implements AfterRemove<Entity>
 */
class Webhook implements AfterSave, AfterRemove
{
    public static int $order = 101;

    public function __construct(
        private Metadata $metadata,
        private WebhookManager $webhookManager,
        private User $user,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (
            $this->toSkip($options, $entity) ||
            !$entity instanceof CoreEntity
        ) {
            return;
        }

        $userIdParam = $entity->isNew() ? SaveOption::CREATED_BY_ID : SaveOption::MODIFIED_BY_ID;

        $eventOptions = new Options(
            userId: $options->get($userIdParam) ?? $this->user->getId(),
        );

        if ($entity->isNew()) {
            $this->webhookManager->processCreate($entity, $eventOptions);

            return;
        }

        $this->webhookManager->processUpdate($entity, $eventOptions);
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        if (
            $this->toSkip($options, $entity) ||
            !$entity instanceof CoreEntity
        ) {
            return;
        }

        $eventOptions = new Options(
            userId: $options->get(SaveOption::MODIFIED_BY_ID) ?? $this->user->getId(),
        );

        $this->webhookManager->processDelete($entity, $eventOptions);
    }

    private function toSkip(SaveOptions|RemoveOptions $options, Entity $entity): bool
    {
        return $options->get(SaveOption::SILENT) ||
            !$this->metadata->get("scopes.{$entity->getEntityType()}.object");
    }
}
