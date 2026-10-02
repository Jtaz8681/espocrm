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

namespace Espo\Core\ORM;

use Espo\ORM\Entity;
use Espo\ORM\Metadata;
use Espo\ORM\Repository\RDBRepository;
use Espo\ORM\Repository\Repository;
use Espo\ORM\Executor\SqlExecutor;
use Espo\Core\Container;

class EntityManagerProxy
{
    private ?EntityManager $entityManager = null;

    public function __construct(private Container $container)
    {}

    private function getEntityManager(): EntityManager
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->container->getByClass(EntityManager::class);
        }

        return $this->entityManager;
    }

    public function getNewEntity(string $entityType): Entity
    {
        return $this->getEntityManager()->getNewEntity($entityType);
    }

    public function getEntityById(string $entityType, string $id): ?Entity
    {
        return $this->getEntityManager()->getEntityById($entityType, $id);
    }

    /**
     * @deprecated As of v9.0.
     * @todo Remove in v11.0.
     */
    public function getEntity(string $entityType, ?string $id = null): ?Entity
    {
        /** @noinspection PhpDeprecationInspection */
        return $this->getEntityManager()->getEntity($entityType, $id);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function saveEntity(Entity $entity, array $options = []): void
    {
        $this->getEntityManager()->saveEntity($entity, $options);
    }

    /**
     * @return Repository<Entity>
     */
    public function getRepository(string $entityType): Repository
    {
        return $this->getEntityManager()->getRepository($entityType);
    }

    /**
     * @return RDBRepository<Entity>
     */
    public function getRDBRepository(string $entityType): RDBRepository
    {
        return $this->getEntityManager()->getRDBRepository($entityType);
    }

    public function getMetadata(): Metadata
    {
        return $this->getEntityManager()->getMetadata();
    }

    public function getSqlExecutor(): SqlExecutor
    {
        return $this->getEntityManager()->getSqlExecutor();
    }

    /**
     * Get an RDB repository by an entity class name.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return RDBRepository<T>
     */
    public function getRDBRepositoryByClass(string $className): RDBRepository
    {
        return $this->getEntityManager()->getRDBRepositoryByClass($className);
    }

    /**
     * Get a repository by an entity class name.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return Repository<T>
     */
    public function getRepositoryByClass(string $className): Repository
    {
        return $this->getEntityManager()->getRepositoryByClass($className);
    }
}
