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

use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Core\Field\DateTime;
use Espo\Core\Field\Link;
use Espo\Tools\Export\Params;
use RuntimeException;

class Export extends Entity
{
    public const string ENTITY_TYPE = 'Export';
    public const string STATUS_PENDING = 'Pending';
    public const string STATUS_RUNNING = 'Running';
    public const string STATUS_SUCCESS = 'Success';
    public const string STATUS_FAILED = 'Failed';

    public function getParams(): Params
    {
        $raw = $this->get('params');

        if (!is_string($raw)) {
            throw new RuntimeException("No 'params'.");
        }

        return Params::fromSerializedRaw($raw);
    }

    public function getStatus(): string
    {
        $value = $this->get('status');

        if (!is_string($value)) {
            throw new RuntimeException("No 'status'.");
        }

        return $value;
    }

    public function getAttachmentId(): ?string
    {
        /** @var ?string */
        return $this->get('attachmentId');
    }

    public function notifyOnFinish(): bool
    {
        return (bool) $this->get('notifyOnFinish');
    }

    public function getCreatedAt(): DateTime
    {
        $value = $this->getValueObject(Field::CREATED_AT);

        if (!$value instanceof DateTime) {
            throw new RuntimeException("No 'createdAt'.");
        }

        return $value;
    }

    public function getCreatedBy(): Link
    {
        $value = $this->getValueObject(Field::CREATED_BY);

        if (!$value instanceof Link) {
            throw new RuntimeException("No 'createdBy'.");
        }

        return $value;
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);

        return $this;
    }

    public function setAttachmentId(string $attachmentId): self
    {
        $this->set('attachmentId', $attachmentId);

        return $this;
    }

    public function setNotifyOnFinish(bool $notifyOnFinish = true): self
    {
        $this->set('notifyOnFinish', $notifyOnFinish);

        return $this;
    }
}
