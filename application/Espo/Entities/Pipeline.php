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

use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\ORM\EntityCollection;

class Pipeline extends Entity
{
    public const string ENTITY_TYPE = 'Pipeline';

    public const string FIELD_STATUS = 'status';
    public const string FIELD_ENTITY_TYPE = 'entityType';
    public const string FIELD_FIELD = 'field';
    public const string FIELD_COLOR = 'color';
    public const string FIELD_IS_AVAILABLE_FOR_ALL = 'isAvailableForAll';
    public const string FIELD_ORDER = 'order';

    public const string LINK_STAGES = 'stages';

    public const string STATUS_ACTIVE = 'Active';

    public function getName(): string
    {
        return $this->get(Field::NAME);
    }

    public function getTargetEntityType(): string
    {
        return $this->get(self::FIELD_ENTITY_TYPE);
    }

    public function getTargetField(): string
    {
        return $this->get(self::FIELD_FIELD);
    }

    public function getColor(): ?int
    {
        return $this->get(self::FIELD_COLOR);
    }

    public function isAvailableForAll(): bool
    {
        return $this->get(self::FIELD_IS_AVAILABLE_FOR_ALL);
    }

    public function setTeams(LinkMultiple $teams): self
    {
        return $this->setValueObject(Field::TEAMS, $teams);
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(Field::TEAMS);
    }

    public function getOrder(): int
    {
        return (int) $this->get(self::FIELD_ORDER);
    }

    /**
     * @return EntityCollection<PipelineStage>
     */
    public function getStages(): EntityCollection
    {
        /** @var EntityCollection<PipelineStage> */
        return $this->relations->getMany(self::LINK_STAGES);
    }
}
