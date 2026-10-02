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

use InvalidArgumentException;

class EmailAddress extends Entity
{
    public const ENTITY_TYPE = 'EmailAddress';

    public const RELATION_ENTITY_EMAIL_ADDRESS = 'EntityEmailAddress';

    /**
     * @param string $value
     * @return void
     */
    protected function _setName($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Not valid email address '{$value}'");
        }

        $this->setInContainer(Field::NAME, $value);

        $this->set('lower', strtolower($value));
    }

    public function getAddress(): string
    {
        return $this->get(Field::NAME);
    }

    public function getLower(): string
    {
        return $this->get('lower');
    }

    public function isOptedOut(): bool
    {
        return $this->get('optOut');
    }

    public function isInvalid(): bool
    {
        return $this->get('invalid');
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

    public function setAddress(string $address): self
    {
        $this->set(Field::NAME, $address);

        return $this;
    }
}
