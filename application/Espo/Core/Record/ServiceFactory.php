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

use Espo\Core\ServiceFactory as Factory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Util as RepositoryUtil;

use RuntimeException;

/**
 * Create a service for a specific user.
 */
class ServiceFactory
{
    private const RECORD_SERVICE_NAME = 'Record';
    private const RECORD_TREE_SERVICE_NAME = 'RecordTree';

    /** @var array<string, string> */
    private $defaultTypeMap = [
        'CategoryTree' => self::RECORD_TREE_SERVICE_NAME,
    ];

    public function __construct(
        private Factory $serviceFactory,
        private Metadata $metadata,
        private User $user,
        private Acl $acl,
        private AclManager $aclManager
    ) {}

    /**
     * Create a record service by an entity class name.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return Service<T>
     */
    public function createByClass(string $className): Service
    {
        $entityType = RepositoryUtil::getEntityTypeByClass($className);

        /** @var Service<T> */
        return $this->create($entityType);
    }

    /**
     * Create a record service for a user by an entity class name.
     *
     * @template T of Entity
     * @param class-string<T> $className An entity class name.
     * @return Service<T>
     */
    public function createByClassForUser(string $className, User $user): Service
    {
        $entityType = RepositoryUtil::getEntityTypeByClass($className);

        /** @var Service<T> */
        return $this->createForUser($entityType, $user);
    }

    /**
     * Create a record service by an entity type.
     *
     * @return Service<Entity>
     */
    public function create(string $entityType): Service
    {
        $obj = $this->createInternal($entityType);

        $obj->setUser($this->user);
        $obj->setAcl($this->acl);

        return $obj;
    }

    /**
     * Create a record service for a user.
     *
     * @return Service<Entity>
     */
    public function createForUser(string $entityType, User $user): Service
    {
        $obj = $this->createInternal($entityType);

        $acl = $this->aclManager->createUserAcl($user);

        $obj->setUser($user);
        $obj->setAcl($acl);

        return $obj;
    }

    /**
     * @return Service<Entity>
     */
    private function createInternal(string $entityType): Service
    {
        if (!$this->metadata->get(['scopes', $entityType, 'entity'])) {
            throw new RuntimeException("Can't create record service '{$entityType}', there's no such entity type.");
        }

        if (!$this->serviceFactory->checkExists($entityType)) {
            return $this->createDefault($entityType);
        }

        $service = $this->serviceFactory->createWith($entityType, ['entityType' => $entityType]);

        if (!$service instanceof Service) {
            return $this->createDefault($entityType);
        }

        return $service;
    }

    /**
     * @return Service<Entity>
     */
    private function createDefault(string $entityType): Service
    {
        $default = self::RECORD_SERVICE_NAME;

        $type = $this->metadata->get(['scopes', $entityType, 'type']);

        if ($type) {
            $default = $this->defaultTypeMap[$type] ?? $default;
        }

        $obj = $this->serviceFactory->createWith($default, ['entityType' => $entityType]);

        if (!$obj instanceof Service) {
            throw new RuntimeException("Service class {$default} is not instance of Record.");
        }

        return $obj;
    }
}
