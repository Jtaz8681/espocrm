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

use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Core\Sms\Sms as SmsInterface;
use Espo\Core\Field\DateTime;

use Espo\Repositories\Sms as SmsRepository;

use RuntimeException;

class Sms extends Entity implements SmsInterface
{
    public const ENTITY_TYPE = 'Sms';

    public const STATUS_ARCHIVED = 'Archived';
    public const STATUS_SENT = 'Sent';
    public const STATUS_SENDING = 'Sending';
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_FAILED = 'Failed';

    public function getDateSent(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject('dateTime');
    }

    public function getCreatedAt(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject(Field::CREATED_AT);
    }

    public function getBody(): string
    {
        return $this->get('body') ?? '';
    }

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function setBody(?string $body): self
    {
        $this->set('body', $body);

        return $this;
    }

    public function setFromNumber(?string $number): self
    {
        $this->set('from', $number);

        return $this;
    }

    public function addToNumber(string $number): self
    {
        $list = $this->getToNumberList();

        $list[] = $number;

        $this->set('to', implode(';', $list));

        return $this;
    }

    public function getFromNumber(): ?string
    {
        if (!$this->hasInContainer('from') && !$this->isNew()) {
            $this->getSmsRepository()->loadFromField($this);
        }

        return $this->get('from');
    }

    public function getFromName(): ?string
    {
        return $this->get('fromName');
    }

    /**
     * @return string[]
     */
    public function getToNumberList(): array
    {
        if (!$this->hasInContainer('to') && !$this->isNew()) {
            $this->getSmsRepository()->loadToField($this);
        }

        $value = $this->get('to');

        if (!$value) {
            return [];
        }

        return explode(';', $value);
    }

    public function setAsSent(): self
    {
        $this->set('status', Sms::STATUS_SENT);

        if (!$this->get('dateSent')) {
            $this->set('dateSent', DateTime::createNow()->toString());
        }

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);

        return $this;
    }

    private function getSmsRepository(): SmsRepository
    {
        if (!$this->entityManager) {
            throw new RuntimeException();
        }

        /** @var SmsRepository */
        return $this->entityManager->getRepository(Sms::ENTITY_TYPE);
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(Field::TEAMS);
    }
}
