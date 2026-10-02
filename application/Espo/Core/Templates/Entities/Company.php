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

namespace Espo\Core\Templates\Entities;

use Espo\Core\Field\Address;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Field\PhoneNumberGroup;
use Espo\Core\ORM\Entity;

class Company extends Entity
{
    public const TEMPLATE_TYPE = 'Company';

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

    public function setEmailAddressGroup(EmailAddressGroup $group): static
    {
        return $this->setValueObject('emailAddress', $group);
    }

    public function setPhoneNumberGroup(PhoneNumberGroup $group): static
    {
        return  $this->setValueObject('phoneNumber', $group);
    }

    public function getBillingAddress(): Address
    {
        /** @var Address */
        return $this->getValueObject('billingAddress');
    }

    public function setBillingAddress(Address $address): static
    {
        return $this->setValueObject('billingAddress', $address);
    }

    public function getShippingAddress(): Address
    {
        /** @var Address */
        return $this->getValueObject('shippingAddress');
    }

    public function setShippingAddress(Address $address): static
    {
        return $this->setValueObject('shippingAddress', $address);
    }
}

