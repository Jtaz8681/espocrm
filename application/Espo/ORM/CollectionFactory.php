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

namespace Espo\ORM;

use Espo\ORM\Query\Select;

/**
 * Creates collections.
 */
class CollectionFactory
{
    public function __construct(protected EntityManager $entityManager)
    {}

    /**
     * Create.
     *
     * @param array<Entity|array<string, mixed>> $dataList
     * @return EntityCollection<Entity>
     */
    public function create(?string $entityType = null, array $dataList = []): EntityCollection
    {
        return new EntityCollection($dataList, $entityType, $this->entityManager->getEntityFactory());
    }

    /**
     * Create from an SQL.
     *
     * @return SthCollection<Entity>
     */
    public function createFromSql(string $entityType, string $sql): SthCollection
    {
        return SthCollection::fromSql($entityType, $sql, $this->entityManager);
    }

    /**
     * Create from a query.
     *
     * @return SthCollection<Entity>
     */
    public function createFromQuery(Select $query): SthCollection
    {
        return SthCollection::fromQuery($query, $this->entityManager);
    }

    /**
     * Create EntityCollection from SthCollection.
     *
     * @template TEntity of Entity
     * @param SthCollection<TEntity> $sthCollection
     * @return EntityCollection<TEntity>
     */
    public function createFromSthCollection(SthCollection $sthCollection): EntityCollection
    {
        /** @var EntityCollection<TEntity> */
        return EntityCollection::fromSthCollection($sthCollection);
    }
}
