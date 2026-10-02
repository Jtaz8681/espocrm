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

namespace Espo\Modules\Crm\Entities;

use Espo\Core\ORM\Entity;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use UnexpectedValueException;

class EmailQueueItem extends Entity
{
    public const ENTITY_TYPE = 'EmailQueueItem';

    public const STATUS_PENDING = 'Pending';
    public const STATUS_FAILED = 'Failed';
    public const STATUS_SENT = 'Sent';
    public const STATUS_SENDING = 'Sending';

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function getAttemptCount(): int
    {
        return (int) $this->get('attemptCount');
    }

    public function isTest(): bool
    {
        return (bool) $this->get('isTest');
    }

    public function getTargetType(): string
    {
        $value = $this->get('targetType');

        if (!is_string($value)) {
            throw new UnexpectedValueException();
        }

        return $value;
    }

    public function getTargetId(): string
    {
        $value = $this->get('targetId');

        if (!is_string($value)) {
            throw new UnexpectedValueException();
        }

        return $value;
    }

    public function getMassEmail(): ?MassEmail
    {
        /** @var ?MassEmail */
        return $this->relations->getOne('massEmail');
    }

    public function getMassEmailId(): ?string
    {
        return $this->get('massEmailId');
    }

    public function getEmailAddress(): ?string
    {
        return $this->get('emailAddress');
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);

        return $this;
    }

    public function setSentAtNow(): self
    {
        $this->set('sentAt', date(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT));

        return $this;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->set('emailAddress', $emailAddress);

        return $this;
    }

    public function incrementAttemptCount(): self
    {
        $attemptCount = $this->getAttemptCount();
        $attemptCount++;

        $this->set('attemptCount', $attemptCount);

        return $this;
    }
}
