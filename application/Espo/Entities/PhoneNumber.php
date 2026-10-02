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
use InvalidArgumentException;

use Espo\Core\ORM\Entity;

class PhoneNumber extends Entity
{
    public const ENTITY_TYPE = 'PhoneNumber';

    public const RELATION_ENTITY_PHONE_NUMBER = 'EntityPhoneNumber';

    /**
     * @param string $value
     * @return void
     */
    protected function _setName($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Phone number can't be empty");
        }

        $this->setInContainer(Field::NAME, $value);
    }

    public function getNumber(): string
    {
        return $this->get(Field::NAME);
    }

    public function isOptedOut(): bool
    {
        return $this->get('optOut');
    }

    public function isInvalid(): bool
    {
        return $this->get('invalid');
    }

    public function getType(): ?string
    {
        return $this->get('type');
    }

    public function setType(string $type): self
    {
        $this->set('type', $type);

        return $this;
    }

    public function setNumber(string $number): self
    {
        $this->set(Field::NAME, $number);

        return $this;
    }

    public function setOptedOut(bool $optedOut): self
    {
        $this->set('optOut', $optedOut);

        return $this;
    }

    public function setInvalid(bool $invalid): self
    {
        $this->set('invalid', $invalid);

        return $this;
    }
}

