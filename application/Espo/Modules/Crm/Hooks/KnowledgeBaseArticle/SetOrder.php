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

namespace Espo\Modules\Crm\Hooks\KnowledgeBaseArticle;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<KnowledgeBaseArticle>
 */
class SetOrder implements BeforeSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $order = $entity->getOrder();

        if (is_null($order)) {
            $order = $this->entityManager
                ->getRDBRepository($entity->getEntityType())
                ->min('order');

            if (!$order) {
                $order = 9999;
            }

            $order--;

            $entity->set('order', $order);
        }
    }
}
