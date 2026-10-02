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

use Espo\Core\ORM\Entity as BaseEntity;
use Espo\Core\Repositories\Database as DatabaseRepository;
use Espo\Core\Utils\ClassFinder;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity as Entity;
use Espo\ORM\EventDispatcher;
use Espo\ORM\Repository\Repository as Repository;

class ClassNameProvider
{
    /** @var class-string<Entity> */
    private const DEFAULT_ENTITY_CLASS_NAME = BaseEntity::class;
    /** @var class-string<Repository<Entity>> */
    private const DEFAULT_REPOSITORY_CLASS_NAME = DatabaseRepository::class;

    /** @var array<string, class-string<Entity>> */
    private array $entityCache = [];

    /** @var array<string, class-string<Repository<Entity>>> */
    private array $repositoryCache = [];

    public function __construct(
        private Metadata $metadata,
        private ClassFinder $classFinder,
        EventDispatcher $eventDispatcher,
    ) {
        $eventDispatcher->subscribeToMetadataUpdate(function () {
            $this->entityCache = [];
            $this->repositoryCache = [];

            $this->classFinder->resetRuntimeCache();
        });
    }

    /**
     * @param string $entityType
     * @return class-string<Entity>
     */
    public function getEntityClassName(string $entityType): string
    {
        if (!array_key_exists($entityType, $this->entityCache)) {
            $this->entityCache[$entityType] = $this->findEntityClassName($entityType);
        }

        return $this->entityCache[$entityType];
    }

    /**
     * @param string $entityType
     * @return class-string<Repository<Entity>>
     */
    public function getRepositoryClassName(string $entityType): string
    {
        if (!array_key_exists($entityType, $this->entityCache)) {
            $this->repositoryCache[$entityType] = $this->findRepositoryClassName($entityType);
        }

        return $this->repositoryCache[$entityType];
    }

    /**
     * @param string $entityType
     * @return class-string<Entity>
     */
    private function findEntityClassName(string $entityType): string
    {
        /** @var ?class-string<Entity> $className */
        $className = $this->metadata->get("entityDefs.$entityType.entityClassName");

        if ($className) {
            return $className;
        }

        /** @var ?class-string<Entity> $className */
        $className = $this->classFinder->find('Entities', $entityType);

        if ($className) {
            return $className;
        }

        /** @var ?string $template */
        $template = $this->metadata->get(['scopes', $entityType, 'type']);

        if ($template) {
            /** @var ?class-string<Entity> $className */
            $className = $this->metadata->get(['app', 'entityTemplates', $template, 'entityClassName']);
        }

        if ($className) {
            return $className;
        }

        return self::DEFAULT_ENTITY_CLASS_NAME;
    }

    /**
     * @param string $entityType
     * @return class-string<Repository<Entity>>
     */
    private function findRepositoryClassName(string $entityType): string
    {
        /** @var ?class-string<Repository<Entity>> $className */
        $className = $this->metadata->get("entityDefs.$entityType.repositoryClassName");

        if ($className) {
            return $className;
        }

        /** @var ?class-string<Repository<Entity>> $className */
        $className = $this->classFinder->find('Repositories', $entityType);

        if ($className) {
            return $className;
        }

        /** @var ?string $template */
        $template = $this->metadata->get(['scopes', $entityType, 'type']);

        if ($template) {
            /** @var ?class-string<Repository<Entity>> $className */
            $className = $this->metadata->get(['app', 'entityTemplates', $template, 'repositoryClassName']);
        }

        if ($className) {
            return $className;
        }

        return self::DEFAULT_REPOSITORY_CLASS_NAME;
    }
}
