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

namespace Espo\Hooks\Pipeline;

use Espo\Core\Field\LinkMultiple;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Pipeline;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Pipeline>
 */
class SetFields implements BeforeSave
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isAvailableForAll()) {
            $entity->setTeams(LinkMultiple::create());
        }

        if ($entity->isNew()) {
            $entityType = $entity->getTargetEntityType();

            $field = $this->metadata->get("scopes.$entityType.statusField");

            $entity->set(Pipeline::FIELD_FIELD, $field);
        }
    }
}
