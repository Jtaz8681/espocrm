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

namespace Espo\Hooks\GroupEmailFolder;

use Espo\Entities\GroupEmailFolder;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class Order
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param GroupEmailFolder $entity
     */
    public function beforeSave(Entity $entity): void
    {
        $order = $entity->getOrder();

        if ($order !== null) {
            return;
        }

        $order = $this->entityManager
            ->getRDBRepositoryByClass(GroupEmailFolder::class)
            ->max('order');

        if (!$order) {
            $order = 0;
        }

        $order++;

        $entity->set('order', $order);
    }
}
