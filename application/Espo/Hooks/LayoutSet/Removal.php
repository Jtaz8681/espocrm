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

namespace Espo\Hooks\LayoutSet;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use Espo\Entities\LayoutSet;
use Espo\Entities\Team;
use Espo\Entities\Portal;

class Removal
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param LayoutSet $entity
     */
    public function afterRemove(Entity $entity): void
    {
        $updateQuery1 = $this->entityManager
            ->getQueryBuilder()
            ->update()
            ->in(Team::ENTITY_TYPE)
            ->set([
                'layoutSetId' => null,
            ])
            ->where([
                'layoutSetId' => $entity->getId(),
            ])
            ->build();

        $this->entityManager
            ->getQueryExecutor()
            ->execute($updateQuery1);

        $updateQuery2 = $this->entityManager
            ->getQueryBuilder()
            ->update()
            ->in(Portal::ENTITY_TYPE)
            ->set([
                'layoutSetId' => null,
            ])
            ->where([
                'layoutSetId' => $entity->getId(),
            ])
            ->build();

        $this->entityManager
            ->getQueryExecutor()
            ->execute($updateQuery2);
    }
}
