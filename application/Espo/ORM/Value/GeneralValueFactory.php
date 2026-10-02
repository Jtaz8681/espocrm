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

namespace Espo\ORM\Value;

use Espo\ORM\Entity;

use RuntimeException;

class GeneralValueFactory
{
    /** @var array<string,?ValueFactory> */
    private array $factoryCache = [];

    public function __construct(private ValueFactoryFactory $valueFactoryFactory)
    {}

    /**
     * Whether a field value object can be created from an entity.
     */
    public function isCreatableFromEntity(Entity $entity, string $field): bool
    {
        $factory = $this->getValueFactory($entity->getEntityType(), $field);

        if (!$factory) {
            return false;
        }

        return $factory->isCreatableFromEntity($entity, $field);
    }

    /**
     * Create a field value object from an entity.
     */
    public function createFromEntity(Entity $entity, string $field): object
    {
        $factory = $this->getValueFactory($entity->getEntityType(), $field);

        if (!$factory) {
            $entityType = $entity->getEntityType();

            throw new RuntimeException("No value-object factory for '{$entityType}.{$field}'.");
        }

        /** @var ValueFactory */
        return $factory->createFromEntity($entity, $field);
    }

    private function getValueFactory(string $entityType, string $field): ?ValueFactory
    {
        $key = $entityType . '_' . $field;

        if (!array_key_exists($key, $this->factoryCache)) {
            $this->factoryCache[$key] = $this->getValueFactoryNoCache($entityType, $field);
        }

        return $this->factoryCache[$key];
    }

    private function getValueFactoryNoCache(string $entityType, string $field): ?ValueFactory
    {
        if (!$this->valueFactoryFactory->isCreatable($entityType, $field)) {
            return null;
        }

        return $this->valueFactoryFactory->create($entityType, $field);
    }
}
