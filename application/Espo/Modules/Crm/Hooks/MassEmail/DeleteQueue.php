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

namespace Espo\Modules\Crm\Hooks\MassEmail;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Modules\Crm\Entities\EmailQueueItem;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\RemoveOptions;

/**
 * @implements AfterRemove<MassEmail>
 */
class DeleteQueue implements AfterRemove
{
    public function __construct(private EntityManager $entityManager) {}

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $deleteQuery = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(EmailQueueItem::ENTITY_TYPE)
            ->where(['massEmailId' => $entity->getId()])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($deleteQuery);
    }
}
