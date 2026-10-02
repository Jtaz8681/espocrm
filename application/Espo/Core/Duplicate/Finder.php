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

namespace Espo\Core\Duplicate;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\SelectBuilderFactory;

use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\WhereItem;
use RuntimeException;

class Finder
{
    private const LIMIT = 5;

    /** @var array<string, ?WhereBuilder<Entity>> */
    private array $whereBuilderMap = [];

    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private WhereBuilderFactory $whereBuilderFactory
    ) {}

    /**
     * Check whether an entity has a duplicate.
     */
    public function check(Entity $entity): bool
    {
        $where = $this->getWhere($entity);

        if (!$where) {
            return false;
        }

        return $this->checkByWhere($entity, $where);
    }

    /**
     * Find entity duplicates.
     *
     * @return ?Collection<Entity>
     */
    public function find(Entity $entity): ?Collection
    {
        $where = $this->getWhere($entity);

        if (!$where) {
            return null;
        }

        return $this->findByWhere($entity, $where);
    }

    /**
     * The method is public for backward compatibility.
     */
    public function checkByWhere(Entity $entity, WhereItem $where): bool
    {
        $entityType = $entity->getEntityType();

        if ($entity->hasId()) {
            $where = Cond::and(
                $where,
                Cond::notEqual(
                    Cond::column(Attribute::ID),
                    $entity->getId()
                )
            );
        }

        $duplicate = $this->entityManager
            ->getRDBRepository($entityType)
            ->where($where)
            ->select(Attribute::ID)
            ->findOne();

        return (bool) $duplicate;
    }

    /**
     * The method is public for backward compatibility.
     *
     * @return ?Collection<Entity>
     */
    public function findByWhere(Entity $entity, WhereItem $where): ?Collection
    {
        $entityType = $entity->getEntityType();

        if ($entity->hasId()) {
            $where = Cond::and(
                $where,
                Cond::notEqual(
                    Cond::column(Attribute::ID),
                    $entity->getId()
                )
            );
        }

        try {
            $baseQueryBuilder = $this->selectBuilderFactory
                ->create()
                ->from($entityType)
                ->withStrictAccessControl()
                ->buildQueryBuilder()
                ->select([Attribute::ID])
                ->limit(0, self::LIMIT);
        } catch (Forbidden|BadRequest $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        $repository = $this->entityManager->getRDBRepository($entityType);

        $query = $baseQueryBuilder
            ->where($where)
            ->build();

        $rdbBuilder = $repository->clone($query);

        if (!$rdbBuilder->findOne()) {
            return null;
        }

        $ids = array_map(
            fn(Entity $e) => $e->getId(),
            iterator_to_array($rdbBuilder->find())
        );

        return $repository
            ->clone($baseQueryBuilder->build())
            ->select(['*'])
            ->where([Attribute::ID => $ids])
            ->find();
    }

    private function getWhere(Entity $entity): ?WhereItem
    {
        $entityType = $entity->getEntityType();

        if (!array_key_exists($entityType, $this->whereBuilderMap)) {
            $this->whereBuilderMap[$entityType] = $this->loadWhereBuilder($entityType);
        }

        $builder = $this->whereBuilderMap[$entityType];

        return $builder?->build($entity);
    }

    /**
     * @return ?WhereBuilder<Entity>
     */
    private function loadWhereBuilder(string $entityType): ?WhereBuilder
    {
        if (!$this->whereBuilderFactory->has($entityType)) {
            return null;
        }

        return $this->whereBuilderFactory->create($entityType);
    }
}
