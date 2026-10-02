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

namespace Espo\Core\Record;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Fetches entities.
 *
 * @since 8.1.0
 */
class EntityProvider
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl
    ) {}

    /**
     * Fetch an entity.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return T
     * @throws NotFound A record not found.
     * @throws Forbidden Read is forbidden for a current user.
     * @since 8.3.0
     * @noinspection PhpDocSignatureInspection
     */
    public function getByClass(string $className, string $id): Entity
    {
        $entity = $this->entityManager
            ->getRDBRepositoryByClass($className)
            ->getById($id);

        return $this->processGet($entity);
    }

    /**
     * Fetch an entity by an entity type.
     *
     * @return Entity
     * @throws NotFound
     * @throws Forbidden
     * @since 9.0.0
     */
    public function get(string $entityType, string $id): Entity
    {
        $entity = $this->entityManager->getEntityById($entityType, $id);

        return $this->processGet($entity);
    }

    /**
     * @template T of Entity
     * @param ?T $entity
     * @return T
     * @throws Forbidden
     * @throws NotFound
     * @noinspection PhpDocSignatureInspection
     */
    private function processGet(?Entity $entity): Entity
    {
        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityRead($entity)) {
            throw new Forbidden();
        }

        return $entity;
    }
}
