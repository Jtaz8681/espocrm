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

namespace Espo\Tools\Kanban;

use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Id\RecordIdGenerator;
use Espo\Core\Utils\Metadata;

class Orderer
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private RecordIdGenerator $idGenerator,
        private MetadataProvider $metadataProvider,
    ) {}

    public function setEntityType(string $entityType): OrdererProcessor
    {
        return $this->createProcessor()->setEntityType($entityType);
    }

    public function setGroup(string $group): OrdererProcessor
    {
        return $this->createProcessor()->setGroup($group);
    }

    public function setUserId(string $userId): OrdererProcessor
    {
        return $this->createProcessor()->setUserId($userId);
    }

    public function setMaxNumber(?int $maxNumber): OrdererProcessor
    {
        return $this->createProcessor()->setMaxNumber($maxNumber);
    }

    public function createProcessor(): OrdererProcessor
    {
        return new OrdererProcessor(
            entityManager: $this->entityManager,
            metadata: $this->metadata,
            idGenerator: $this->idGenerator,
            metadataProvider: $this->metadataProvider,
        );
    }
}
