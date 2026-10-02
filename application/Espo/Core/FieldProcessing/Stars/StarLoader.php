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

namespace Espo\Core\FieldProcessing\Stars;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Tools\Stars\StarService;

/**
 * @implements Loader<Entity>
 */
class StarLoader implements Loader
{
    public function __construct(
        private StarService $service,
        private User $user
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        if (
            !$entity->hasAttribute(Field::IS_STARRED) ||
            !$this->service->isEnabled($entity->getEntityType())
        ) {
            return;
        }

        $entity->set(Field::IS_STARRED, $this->service->isStarred($entity, $this->user));
    }
}
