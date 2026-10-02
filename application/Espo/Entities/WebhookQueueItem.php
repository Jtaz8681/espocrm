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

use Espo\Core\Field\DateTime;
use Espo\Core\Field\LinkParent;
use Espo\Core\ORM\Entity;
use stdClass;

class WebhookQueueItem extends Entity
{
    public const ENTITY_TYPE = 'WebhookQueueItem';

    public const STATUS_PENDING = 'Pending';
    public const STATUS_SUCCESS = 'Success';
    public const STATUS_FAILED = 'Failed';

    public function getAttempts(): int
    {
        return $this->get('attempts') ?? 0;
    }

    public function setStatus(string $status): self
    {
        return $this->set('status', $status);
    }

    public function setData(stdClass $data): self
    {
        return $this->set('data', $data);
    }

    public function setAttempts(?int $attempts): self
    {
        return $this->set('attempts', $attempts);
    }

    public function setProcessAt(?DateTime $processAt): self
    {
        if (!$processAt) {
            $this->set('processAt', $processAt);

            return $this;
        }

        $this->set('processAt', $processAt->toString());

        return $this;
    }

    public function setProcessedAt(?DateTime $processedAt): self
    {
        if (!$processedAt) {
            $this->set('processedAt', $processedAt);

            return $this;
        }

        $this->set('processedAt', $processedAt->toString());

        return $this;
    }

    public function getTargetType(): ?string
    {
        return $this->get('targetType');
    }

    public function getTargetId(): ?string
    {
        return $this->get('targetId');
    }

    public function getWebhookId(): string
    {
        return $this->get('webhookId') ?? '';
    }

    public function getData(): stdClass
    {
        return $this->get('data') ?? (object) [];
    }

    public function setEvent(string $event): self
    {
        return $this->set('event', $event);
    }

    public function setWebhook(Webhook $webhook): self
    {
        return $this->setRelatedLinkOrEntity('webhook', $webhook);
    }

    public function setTarget(Entity|LinkParent $target): self
    {
        return $this->setRelatedLinkOrEntity('target', $target);
    }
}
