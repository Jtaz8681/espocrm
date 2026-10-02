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

class AddressCountry extends Entity
{
    public const ENTITY_TYPE = 'AddressCountry';

    public function getName(): string
    {
        return $this->get(Field::NAME);
    }

    public function getCode(): string
    {
        return $this->get('code');
    }

    public function isPreferred(): bool
    {
        return (bool) $this->get('isPreferred');
    }
}
