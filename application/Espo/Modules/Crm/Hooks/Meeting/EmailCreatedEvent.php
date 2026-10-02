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

namespace Espo\Modules\Crm\Hooks\Meeting;

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Entities\Email;
use Espo\ORM\EntityManager;
use Espo\ORM\Entity;

class EmailCreatedEvent
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterRemove(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::SILENT])) {
            return;
        }

        $updateQuery = $this->entityManager
            ->getQueryBuilder()
            ->update()
            ->in(Email::ENTITY_TYPE)
            ->set([
                'createdEventId' => null,
                'createdEventType' => null,
            ])
            ->where([
                'createdEventId' => $entity->getId(),
                'createdEventType' => $entity->getEntityType()
            ])
            ->limit(1)
            ->build();

        $this->entityManager->getQueryExecutor()->execute($updateQuery);
    }
}
