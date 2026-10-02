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

use Espo\Core\Field\Address;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Field\PhoneNumberGroup;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Entities\User;

class Account extends Entity
{
    public const ENTITY_TYPE = 'Account';

    /**
     * @since 10.0.7
     */
    public const string FILED_TYPE = 'type';

    public const TYPE_CUSTOMER = 'Customer';
    public const TYPE_PARTNER = 'Partner';
    public const TYPE_RESELLER = 'Reseller';

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function setName(?string $name): self
    {
        $this->set(Field::NAME, $name);

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->get('emailAddress');
    }

    public function getEmailAddressGroup(): EmailAddressGroup
    {
        /** @var EmailAddressGroup */
        return $this->getValueObject('emailAddress');
    }

    public function getPhoneNumberGroup(): PhoneNumberGroup
    {
        /** @var PhoneNumberGroup */
        return $this->getValueObject('phoneNumber');
    }

    public function setEmailAddressGroup(EmailAddressGroup $group): self
    {
        $this->setValueObject('emailAddress', $group);

        return $this;
    }

    public function setPhoneNumberGroup(PhoneNumberGroup $group): self
    {
        $this->setValueObject('phoneNumber', $group);

        return $this;
    }

    public function getBillingAddress(): Address
    {
        /** @var Address */
        return $this->getValueObject('billingAddress');
    }

    public function setBillingAddress(Address $address): self
    {
        $this->setValueObject('billingAddress', $address);

        return $this;
    }

    public function getShippingAddress(): Address
    {
        /** @var Address */
        return $this->getValueObject('shippingAddress');
    }

    public function setShippingAddress(Address $address): self
    {
        $this->setValueObject('shippingAddress', $address);

        return $this;
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

    public function setAssignedUser(Link|User|null $assignedUser): self
    {
        return $this->setRelatedLinkOrEntity(Field::ASSIGNED_USER, $assignedUser);
    }

    public function setTeams(LinkMultiple $teams): self
    {
        $this->setValueObject(Field::TEAMS, $teams);

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

    /**
     * @since 10.0.7
     */
    public function setType(?string $type): self
    {
        return $this->set(self::FILED_TYPE, $type);
    }

    /**
     * @since 10.0.7
     */
    public function getType(): ?string
    {
        return $this->get(self::FILED_TYPE);
    }
}
