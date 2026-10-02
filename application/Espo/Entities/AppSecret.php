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

class AppSecret extends Entity
{
    public const ENTITY_TYPE = 'AppSecret';

    public function getName(): string
    {
        return $this->get(Field::NAME);
    }

    public function getValue(): string
    {
        return (string) $this->get('value');
    }

    public function setName(string $name): self
    {
        $this->set(Field::NAME, $name);

        return $this;
    }

    /**
     * @internal Do not use.
     * @todo Rename to setValue in v9.0.
     */
    public function setSecretValue(string $value): self
    {
        $this->set('value', $value);

        return $this;
    }
}
