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

use Espo\Core\ORM\Entity;
use UnexpectedValueException;

class Template extends Entity
{
    public const ENTITY_TYPE = 'Template';

    public const STATUS_ACTIVE = 'Active';

    public const string FIELD_ENTITY_TYPE = 'entityType';

    public function getTargetEntityType(): string
    {
        $entityType = $this->get(self::FIELD_ENTITY_TYPE);

        if ($entityType === null) {
            throw new UnexpectedValueException();
        }

        return $entityType;
    }

    public function isActive(): bool
    {
        return $this->get('status') === self::STATUS_ACTIVE;
    }

    public function getFilename(): ?string
    {
        return $this->get('filename');
    }
}
