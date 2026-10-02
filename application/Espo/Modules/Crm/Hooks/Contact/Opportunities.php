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

namespace Espo\Modules\Crm\Hooks\Contact;

use Espo\ORM\EntityManager;
use Espo\ORM\Entity;

class Opportunities
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $data
     */
    public function afterRelate(Entity $entity, array $options = [], array $data = []): void
    {
        $relationName = $data['relationName'] ?? null;
        /** @var ?Entity $foreignEntity */
        $foreignEntity = $data['foreignEntity'] ?? null;

        if ($relationName === 'opportunities' && $foreignEntity) {
            if (!$foreignEntity->get('contactId') && $foreignEntity->has('contactId')) {
                $foreignEntity->set('contactId', $entity->getId());

                $this->entityManager->saveEntity($foreignEntity);
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $data
     */
    public function afterUnrelate(Entity $entity, array $options = [], array $data = []): void
    {
        $relationName = $data['relationName'] ?? null;
        /** @var ?Entity $foreignEntity */
        $foreignEntity = $data['foreignEntity'] ?? null;

        if ($relationName === 'opportunities' && $foreignEntity) {
            if ($foreignEntity->get('contactId') && $foreignEntity->get('contactId') === $entity->getId()) {
                $foreignEntity->set('contactId', null);

                $this->entityManager->saveEntity($foreignEntity);
            }
        }
    }
}
