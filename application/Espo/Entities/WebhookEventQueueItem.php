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

namespace Espo\Entities;

use Espo\Core\Field\LinkParent;
use Espo\Core\ORM\Entity;
use stdClass;

class WebhookEventQueueItem extends Entity
{
    public const ENTITY_TYPE = 'WebhookEventQueueItem';

    public function setIsProcessed(bool $isProcessed = true): self
    {
        $this->set('isProcessed', $isProcessed);

        return $this;
    }

    public function getEvent(): string
    {
        return (string) $this->get('event');
    }

    public function getTargetType(): ?string
    {
        return $this->get('targetType');
    }

    public function getTargetId(): ?string
    {
        return $this->get('targetId');
    }

    public function getUserId(): ?string
    {
        return $this->get('userId');
    }

    public function getData(): stdClass
    {
        return $this->get('data') ?? (object) [];
    }

    public function setEvent(string $event): self
    {
        return $this->set('event', $event);
    }

    public function setUserId(?string $userId): self
    {
        return $this->set('userId', $userId);
    }

    public function setTarget(LinkParent|Entity $target): self
    {
        return $this->setRelatedLinkOrEntity('target', $target);
    }

    /**
     * @param stdClass|array<string, mixed> $data
     */
    public function setData(stdClass|array $data): self
    {
        return $this->set('data', $data);
    }
}
