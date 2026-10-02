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

namespace Espo\Core\Entities;

use Espo\Core\Field\Address;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Field\PhoneNumberGroup;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Core\ORM\Helper;

use Espo\ORM\EntityManager;
use Espo\ORM\Relation\Relations;
use Espo\ORM\Value\ValueAccessorFactory;

class Person extends Entity
{
    private Helper $helper;

    public function __construct(
        string $entityType,
        array $defs,
        EntityManager $entityManager,
        Helper $helper,
        ?ValueAccessorFactory $valueAccessorFactory = null,
        ?Relations $relations = null,
    ) {
        parent::__construct(
            $entityType,
            $defs,
            $entityManager,
            $valueAccessorFactory,
            $relations
        );

        $this->helper = $helper;
    }

    /**
     * @param string $value
     * @return void
     */
    protected function _setLastName($value)
    {
        $this->setInContainer('lastName', $value);

        if (!$this->helper->hasAllPersonNameAttributes($this, 'name')) {
            return;
        }

        $name = $this->helper->formatPersonName($this, 'name');

        $this->setInContainer(Field::NAME, $name);
    }

    /**
     * @param string $value
     * @return void
     */
    protected function _setFirstName($value)
    {
        $this->setInContainer('firstName', $value);

        if (!$this->helper->hasAllPersonNameAttributes($this, 'name')) {
            return;
        }

        $name = $this->helper->formatPersonName($this, 'name');

        $this->setInContainer(Field::NAME, $name);
    }

    /**
     * @param string $value
     * @return void
     */
    protected function _setMiddleName($value)
    {
        $this->setInContainer('middleName', $value);

        if (!$this->helper->hasAllPersonNameAttributes($this, 'name')) {
            return;
        }

        $name = $this->helper->formatPersonName($this, 'name');

        $this->setInContainer(Field::NAME, $name);
    }

    /**
     * Get a primary email address.
     */
    public function getEmailAddress(): ?string
    {
        return $this->get('emailAddress');
    }

    /**
     * Get a primary phone number.
     */
    public function getPhoneNumber(): ?string
    {
        return $this->get('phoneNumber');
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

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function getFirstName(): ?string
    {
        return $this->get('firstName');
    }

    public function getLastName(): ?string
    {
        return $this->get('lastName');
    }

    public function getMiddleName(): ?string
    {
        return $this->get('middleName');
    }

    public function setFirstName(?string $firstName): static
    {
        return $this->set('firstName', $firstName);
    }

    public function setLastName(?string $lastName): static
    {
        return $this->set('lastName', $lastName);
    }

    public function setMiddleName(?string $middleName): static
    {
        return $this->set('middleName', $middleName);
    }

    public function setEmailAddressGroup(EmailAddressGroup $group): static
    {
        return $this->setValueObject('emailAddress', $group);
    }

    public function setPhoneNumberGroup(PhoneNumberGroup $group): static
    {
        return $this->setValueObject('phoneNumber', $group);
    }

    public function getAddress(): Address
    {
        /** @var Address */
        return $this->getValueObject('address');
    }

    public function setAddress(Address $address): static
    {
        return $this->setValueObject('address', $address);
    }
}
