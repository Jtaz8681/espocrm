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

namespace Espo\Tools\CategoryTree;

use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;
use Espo\Core\Repositories\CategoryTree;
use Espo\ORM\EntityManager;

/**
 * Rebuild category tree paths.
 */
class RebuildPaths
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Error
     */
    public function run(string $entityType): void
    {
        if (
            !$this->entityManager->hasRepository($entityType) ||
            !$this->entityManager->getRepository($entityType) instanceof CategoryTree
        ) {
            throw new Error("Bad entity type.");
        }

        $this->clearTable($entityType);

        $this->processBranch($entityType, null);
    }

    private function clearTable(string $entityType): void
    {
        $query = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from($entityType . 'Path')
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }

    private function processBranch(string $entityType, ?string $parentId): void
    {
        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->sth()
            ->where(['parentId' => $parentId])
            ->find();

        foreach ($collection as $entity) {
            $this->processEntity($entity);
        }
    }

    private function processEntity(Entity $entity): void
    {
        $parentId = $entity->get('parentId');
        $pathEntityType = $entity->getEntityType() . 'Path';

        if ($parentId) {
            $subSelect1 = $this->entityManager
                ->getQueryBuilder()
                ->select()
                ->from($pathEntityType)
                ->select(['ascendorId', "'" . $entity->getId() . "'"])
                ->where([
                    'descendorId' => $parentId,
                ])
                ->build();

            $insert = $this->entityManager
                ->getQueryBuilder()
                ->insert()
                ->into($pathEntityType)
                ->columns(['ascendorId', 'descendorId'])
                ->valuesQuery($subSelect1)
                ->build();

            $this->entityManager->getQueryExecutor()->execute($insert);
        }

        $insert = $this->entityManager
            ->getQueryBuilder()
            ->insert()
            ->into($pathEntityType)
            ->columns(['ascendorId', 'descendorId'])
            ->values([
                'ascendorId' => $entity->getId(),
                'descendorId' => $entity->getId(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($insert);

        $this->processBranch($entity->getEntityType(), $entity->getId());
    }
}
