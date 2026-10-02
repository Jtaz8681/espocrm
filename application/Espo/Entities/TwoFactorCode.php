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

use Espo\Core\Field\DateTime;
use Espo\Core\Name\Field;

class TwoFactorCode extends \Espo\Core\ORM\Entity
{
    public const ENTITY_TYPE = 'TwoFactorCode';

    public function isActive(): bool
    {
        return $this->get('isActive');
    }

    public function getCreatedAt(): DateTime
    {
        /** @var DateTime */
        return $this->getValueObject(Field::CREATED_AT);
    }

    public function getCode(): string
    {
        return $this->get('code');
    }

    public function getAttemptsLeft(): int
    {
        return $this->get('attemptsLeft');
    }

    public function setInactive(): void
    {
        $this->set('isActive', false);
    }

    public function decrementAttemptsLeft(): void
    {
        $this->set('attemptsLeft', $this->getAttemptsLeft() - 1);
    }
}
