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

use Espo\ORM\Entity;
use Espo\ORM\Repository\Util as RepositoryUtil;

/**
 * Container for record services. Lazy loading is used.
 * Usually there's no need to have multiple record service instances of the same entity type.
 * Use this container instead of serviceFactory to get record services.
 *
 * Important. Returns record services for the current user.
 * Use the service-factory to create services for a specific user.
 */
class ServiceContainer
{
    /** @var array<string, Service<Entity>> */
    private $data = [];

    public function __construct(private ServiceFactory $serviceFactory)
    {}

    /**
     * Get a record service by an entity class name.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return Service<T>
     */
    public function getByClass(string $className): Service
    {
        $entityType = RepositoryUtil::getEntityTypeByClass($className);

        /** @var Service<T> */
        return $this->get($entityType);
    }

    /**
     * Get a record service by an entity type.
     *
     * @return Service<Entity>
     */
    public function get(string $entityType): Service
    {
        if (!array_key_exists($entityType, $this->data)) {
            $this->load($entityType);
        }

        return $this->data[$entityType];
    }

    private function load(string $entityType): void
    {
        $this->data[$entityType] = $this->serviceFactory->create($entityType);
    }
}
