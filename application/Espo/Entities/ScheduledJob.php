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

class ScheduledJob extends Entity
{
    public const string ENTITY_TYPE = 'ScheduledJob';

    public const string STATUS_ACTIVE = 'Active';

    /**
     * @since 10.0.0
     */
    public const string FIELD_JOB = 'job';

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function getScheduling(): ?string
    {
        return $this->get('scheduling');
    }

    public function getJob(): ?string
    {
        return $this->get(self::FIELD_JOB);
    }

    /**
     * @since 10.0.0
     */
    public function setActive(): self
    {
        return $this->set('status', self::STATUS_ACTIVE);
    }

    /**
     * @since 10.0.0
     */
    public function setName(string $name): self
    {
        return $this->set(Field::NAME, $name);
    }

    /**
     * @since 10.0.0
     */
    public function setScheduling(string $scheduling): self
    {
        return $this->set('scheduling', $scheduling);
    }

    /**
     * @since 10.0.0
     */
    public function setJob(string $job): self
    {
        return $this->set(self::FIELD_JOB, $job);
    }
}
