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

use Espo\Core\Field\Link;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

class Team extends Entity
{
    public const ENTITY_TYPE = 'Team';

    public const RELATIONSHIP_ENTITY_TEAM = 'EntityTeam';
    public const RELATIONSHIP_TEAM_USER = 'TeamUser';

    public const LINK_ROLES = 'roles';

    public function getWorkingTimeCalendar(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('workingTimeCalendar');
    }

    public function getLayoutSet(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('layoutSet');
    }

    /**
     * @return string[]
     */
    public function getPositionList(): array
    {
        return $this->get('positionList') ?? [];
    }

    /**
     * @since 10.0.3
     */
    public function setName(string $name): self
    {
        return $this->set(Field::NAME, $name);
    }

    /**
     * @since 10.0.3
     */
    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }
}
