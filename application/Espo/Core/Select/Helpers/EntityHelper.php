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

namespace Espo\Core\Select\Helpers;

use Espo\ORM\BaseEntity;
use Espo\ORM\Defs;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;

/**
 * @internal
 */
class EntityHelper
{
    public function __construct(
        private Defs $defs,
    ) {}

    /**
     * @internal
     */
    public function getRelationEntityType(Entity $entity, string $relation): ?string
    {
        if ($entity instanceof BaseEntity) {
            return $entity->getRelationParam($relation, RelationParam::ENTITY);
        }

        $entityDefs = $this->defs->getEntity($entity->getEntityType());

        if (!$entityDefs->hasRelation($relation)) {
            return null;
        }

        return $entityDefs
            ->getRelation($relation)
            ->tryGetForeignEntityType();
    }
}
