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

use Espo\Core\Entities\Person;
use Espo\Core\Field\DateTime;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Entities\User;

class Lead extends Person
{
    public const ENTITY_TYPE = 'Lead';

    public const STATUS_NEW = 'New';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_IN_PROCESS = 'In Process';
    public const STATUS_CONVERTED = 'Converted';
    public const STATUS_RECYCLED = 'Recycled';
    public const STATUS_DEAD = 'Dead';

    public function get(string $attribute): mixed
    {
        if ($attribute === Field::NAME) {
            return $this->getNameInternal();
        }

        return parent::get($attribute);
    }

    public function has(string $attribute): bool
    {
        if ($attribute === Field::NAME) {
            return $this->hasNameInternal();
        }

        return parent::has($attribute);
    }

    public function setDescription(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function getDescription(): ?string
    {
        return $this->get('description');
    }

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    private function getNameInternal(): ?string
    {
        if (!$this->hasInContainer(Field::NAME) || !$this->getFromContainer(Field::NAME)) {
            if ($this->get('accountName')) {
                return $this->get('accountName');
            }

            if ($this->get('emailAddress')) {
                return $this->get('emailAddress');
            }

            if ($this->get('phoneNumber')) {
                return $this->get('phoneNumber');
            }
        }

        return $this->getFromContainer(Field::NAME);
    }

    private function hasNameInternal(): bool
    {
        if ($this->hasInContainer(Field::NAME)) {
            return true;
        }

        if ($this->has('accountName')) {
            return true;
        }

        if ($this->has('emailAddress')) {
            return true;
        }

        if ($this->has('phoneNumber')) {
            return true;
        }

        return false;
    }

    public function getCampaign(): ?Campaign
    {
        /** @var ?Campaign */
        return $this->relations->getOne('campaign');
    }

    public function getAssignedUser(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::ASSIGNED_USER);
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(Field::TEAMS);
    }

    public function getCreatedAccount(): ?Account
    {
        /** @var ?Account */
        return $this->relations->getOne('createdAccount');
    }

    public function getCreatedContact(): ?Contact
    {
        /** @var ?Contact */
        return $this->relations->getOne('createdContact');
    }

    public function getCreatedOpportunity(): ?Opportunity
    {
        /** @var ?Opportunity */
        return $this->relations->getOne('createdOpportunity');
    }

    public function getConvertedAt(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject('convertedAt');
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);

        return $this;
    }

    public function setCreatedAccount(Account|null $createdAccount): self
    {
        $this->relations->set('createdAccount', $createdAccount);

        return $this;
    }

    public function setAssignedUser(Link|User|null $assignedUser): self
    {
        return $this->setRelatedLinkOrEntity(Field::ASSIGNED_USER, $assignedUser);
    }

    public function setTeams(LinkMultiple $teams): self
    {
        $this->setValueObject(Field::TEAMS, $teams);

        return $this;
    }

    public function setSource(?string $source): self
    {
        return $this->set('source', $source);
    }

    public function setCampaign(Link|Campaign|null $campaign): self
    {
        return $this->setRelatedLinkOrEntity('campaign', $campaign);
    }

    /**
     * @since 9.3.0
     */
    public function getTitle(): ?string
    {
        return $this->get('title');
    }
}
