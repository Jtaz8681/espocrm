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

class PipelineStage extends Entity
{
    public const string ENTITY_TYPE = 'PipelineStage';

    public const string FIELD_MAPPED_STATUS = 'mappedStatus';
    public const string FIELD_ORDER = 'order';
    public const string FIELD_PIPELINE = 'pipeline';
    public const string FIELD_NAME = 'name';

    public const string ATTR_PIPELINE_ID = 'pipelineId';

    public function getName(): string
    {
        return $this->get(self::FIELD_NAME);
    }

    public function setName(string $name): self
    {
        $this->set(self::FIELD_NAME, $name);

        return $this;
    }

    public function setMappedStatus(string $status): self
    {
        $this->set(self::FIELD_MAPPED_STATUS, $status);

        return $this;
    }

    public function getMappedStatus(): string
    {
        return $this->get(self::FIELD_MAPPED_STATUS);
    }

    public function getOrder(): int
    {
        return (int) $this->get(self::FIELD_ORDER);
    }

    public function setOrder(int $order): self
    {
        $this->set(self::FIELD_ORDER, $order);

        return $this;
    }

    public function setPipeline(Pipeline $pipeline): self
    {
        return $this->setRelatedLinkOrEntity(self::FIELD_PIPELINE, $pipeline);
    }

    public function getPipeline(): Pipeline
    {
        $pipeline = $this->relations->getOne(self::FIELD_PIPELINE);

        if (!$pipeline instanceof Pipeline) {
            throw new UnexpectedValueException("No pipeline.");
        }

        return $pipeline;
    }
}
