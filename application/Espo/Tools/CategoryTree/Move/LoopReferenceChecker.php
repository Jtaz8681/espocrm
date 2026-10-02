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

namespace Espo\Tools\CategoryTree\Move;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Templates\Entities\CategoryTree;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class LoopReferenceChecker
{
    private const ATTR_PARENT_ID = 'parentId';

    public function __construct(
        private EntityManager $entityManager,
    ) {}

    /**
     * @throws Forbidden
     */
    public function check(CategoryTree $entity, Entity $reference): void
    {
        $parentId = $reference->get(self::ATTR_PARENT_ID);

        if (!$parentId) {
            return;
        }

        if ($parentId === $entity->getId()) {
            throw new Forbidden("Cannot move. Circle reference.");
        }

        $parent = $this->entityManager->getEntityById($entity->getEntityType(), $parentId);

        if (!$parent) {
            return;
        }

        $this->check($entity, $parent);
    }
}
