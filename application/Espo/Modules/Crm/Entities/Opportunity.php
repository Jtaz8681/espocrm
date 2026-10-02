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

use Espo\Core\Field\Currency;
use Espo\Core\Field\Date;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Entities\User;

class Opportunity extends Entity
{
    public const ENTITY_TYPE = 'Opportunity';

    /**
     * @deprecated
     * @todo Remove in v10.1.
     */
    public const string FIELD_CLOSED_DATE = 'closeDate';

    /** @since 10.0.0 */
    public const string FIELD_CLOSE_DATE = 'closeDate';
    /**@since 10.0.0 */
    public const string FIELD_STAGE = 'stage';
    /** @since 10.0.0 */
    public const string FIELD_AMOUNT = 'amount';

    public const STAGE_CLOSED_WON = 'Closed Won';
    public const STAGE_CLOSED_LOST = 'Closed Lost';

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function setName(?string $name): self
    {
        $this->set(Field::NAME, $name);

        return $this;
    }

    public function setDescription(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function getDescription(): ?string
    {
        return $this->get('description');
    }

    public function getAmount(): ?Currency
    {
        /** @var ?Currency */
        return $this->getValueObject(self::FIELD_AMOUNT);
    }

    public function setAmount(?Currency $amount): self
    {
        $this->setValueObject(self::FIELD_AMOUNT, $amount);

        return $this;
    }

    public function getCloseDate(): ?Date
    {
        /** @var ?Date */
        return $this->getValueObject(self::FIELD_CLOSE_DATE);
    }

    public function setCloseDate(?Date $closeDate): self
    {
        $this->setValueObject(self::FIELD_CLOSE_DATE, $closeDate);

        return $this;
    }

    public function getStage(): ?string
    {
        return $this->get(self::FIELD_STAGE);
    }

    public function setStage(?string $stage): self
    {
        return $this->set(self::FIELD_STAGE, $stage);
    }

    public function getLastStage(): ?string
    {
        return $this->get('lastStage');
    }

    public function setLastStage(?string $lastStage): self
    {
        return $this->set('lastStage', $lastStage);
    }

    public function getProbability(): ?int
    {
        return $this->get('probability');
    }

    public function setProbability(?int $probability): self
    {
        return $this->set('probability', $probability);
    }

    public function getAccount(): ?Account
    {
        /** @var ?Account */
        return $this->relations->getOne('account');
    }

    /**
     * A primary contact.
     */
    public function getContact(): ?Contact
    {
        /** @var ?Contact */
        return $this->relations->getOne('contact');
    }

    public function getContacts(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('contacts');
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

    public function setAccount(Account|Link|null $account): self
    {
        return $this->setRelatedLinkOrEntity('account', $account);
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
}
