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

class NextNumber extends Entity
{
    public const ENTITY_TYPE = 'NextNumber';

    public function getNumberValue(): ?int
    {
        return $this->get('value');
    }

    public function setNumberValue(int $value): self
    {
        return $this->set('value', $value);
    }

    public function setTargetEntityType(string $entityType): self
    {
        return $this->set('entityType', $entityType);
    }

    public function setTargetFieldName(string $fieldName): self
    {
        return $this->set('fieldName', $fieldName);
    }

    public function getTargetEntityType(): string
    {
        return $this->get('entityType');
    }

    public function getTargetFieldName(): string
    {
        return $this->get('fieldName');
    }
}
