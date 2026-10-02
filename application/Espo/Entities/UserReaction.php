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

use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

class UserReaction extends Entity
{
    const ENTITY_TYPE = 'UserReaction';

    public function getType(): string
    {
        return $this->get('type');
    }

    public function getParent(): LinkParent
    {
        /** @var LinkParent */
        return $this->getValueObject(Field::PARENT);
    }

    public function setType(string $type): self
    {
        $this->set('type', $type);

        return $this;
    }

    public function setParent(Note $note): self
    {
        $this->relations->set(Field::PARENT, $note);

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->relations->set('user', $user);

        return $this;
    }
}
