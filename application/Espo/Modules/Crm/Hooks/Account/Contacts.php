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

namespace Espo\Modules\Crm\Hooks\Account;

use Espo\ORM\{
    Entity,
    EntityManager,
};

class Contacts
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

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
        $foreignEntity = $data['foreignEntity'] ?? null;

        if ($relationName === 'contacts' && $foreignEntity) {
            if (!$foreignEntity->get('accountId') && $foreignEntity->has('accountId')) {
                $foreignEntity->set('accountId', $entity->getId());

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
        $foreignEntity = $data['foreignEntity'] ?? null;

        if ($relationName === 'contacts' && $foreignEntity) {
            if ($foreignEntity->get('accountId') && $foreignEntity->get('accountId') === $entity->getId()) {
                $foreignEntity->set('accountId', null);

                $this->entityManager->saveEntity($foreignEntity);
            }
        }
    }
}
