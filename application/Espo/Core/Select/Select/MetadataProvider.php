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

namespace Espo\Core\Select\Select;

use Espo\Core\Utils\Metadata;
use Espo\ORM\EntityManager;

class MetadataProvider
{
    public function __construct(private Metadata $metadata, private EntityManager $entityManager)
    {}

    public function getDefaultOrderBy(string $entityType): ?string
    {
        return $this->metadata->get([
            'entityDefs', $entityType, 'collection', 'orderBy'
        ]) ?? null;
    }

    /**
     * @return ?array<string, string[]>
     */
    public function getSelectAttributesDependencyMap(string $entityType): ?array
    {
        return $this->metadata->get([
            'selectDefs', $entityType, 'selectAttributesDependencyMap'
        ]) ?? null;
    }

    /**
     * @return ?string[]
     */
    public function getAclPortalAttributeList(string $entityType): ?array
    {
        return $this->metadata->get([
            'selectDefs', $entityType, 'aclPortalAttributeList'
        ]) ?? null;
    }

    /**
     * @return ?string[]
     */
    public function getAclAttributeList(string $entityType): ?array
    {
        return $this->metadata->get([
            'selectDefs', $entityType, 'aclAttributeList'
        ]) ?? null;
    }

    public function hasAttribute(string $entityType, string $attribute): bool
    {
        return $this->entityManager
            ->getMetadata()
            ->getDefs()
            ->getEntity($entityType)
            ->hasAttribute($attribute);
    }

    public function isAttributeNotStorable(string $entityType, string $attribute): bool
    {
        return $this->entityManager
            ->getMetadata()
            ->getDefs()
            ->getEntity($entityType)
            ->getAttribute($attribute)
            ->isNotStorable();
    }

    public function getAttributeType(string $entityType, string $attribute): string
    {
        return $this->entityManager
            ->getMetadata()
            ->getDefs()
            ->getEntity($entityType)
            ->getAttribute($attribute)
            ->getType();
    }
}
