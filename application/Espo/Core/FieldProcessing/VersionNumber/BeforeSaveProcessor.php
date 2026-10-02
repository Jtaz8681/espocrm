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

namespace Espo\Core\FieldProcessing\VersionNumber;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;

class BeforeSaveProcessor
{
    private const ATTRIBUTE_VERSION_NUMBER = Field::VERSION_NUMBER;

    public function __construct(private Metadata $metadata)
    {}

    public function process(Entity $entity): void
    {
        $optimisticConcurrencyControl = $this->metadata
            ->get(['entityDefs', $entity->getEntityType(), 'optimisticConcurrencyControl']);

        if (!$optimisticConcurrencyControl) {
            return;
        }

        if ($entity->isNew()) {
            $entity->set(self::ATTRIBUTE_VERSION_NUMBER, 1);

            return;
        }

        $entity->clear(self::ATTRIBUTE_VERSION_NUMBER);

        if (!$entity->hasFetched(self::ATTRIBUTE_VERSION_NUMBER)) {
            return;
        }

        $versionNumber = $entity->getFetched(self::ATTRIBUTE_VERSION_NUMBER);

        if ($versionNumber === null) {
            $versionNumber = 0;
        }

        $versionNumber++;

        $entity->set(self::ATTRIBUTE_VERSION_NUMBER, $versionNumber);
    }
}
