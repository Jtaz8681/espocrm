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

namespace Espo\Core\Select\Text;

use Espo\Core\Utils\Metadata;

use Espo\ORM\Defs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\FieldParam;

class MetadataProvider
{
    private Defs $ormDefs;

    public function __construct(private Metadata $metadata, Defs $ormDefs)
    {
        $this->ormDefs = $ormDefs;
    }

    public function getFullTextSearchOrderType(string $entityType): ?string
    {
        return $this->metadata->get([
            'entityDefs', $entityType, 'collection', 'fullTextSearchOrderType'
        ]);
    }

    /**
     * @return string[]|null
     */
    public function getTextFilterAttributeList(string $entityType): ?array
    {
        return $this->metadata->get([
            'entityDefs', $entityType, 'collection', 'textFilterFields'
        ]);
    }

    public function isFieldNotStorable(string $entityType, string $field): bool
    {
        return (bool) $this->metadata->get([
            'entityDefs', $entityType, 'fields', $field, Defs\Params\FieldParam::NOT_STORABLE
        ]);
    }

    public function isFullTextSearchSupportedForField(string $entityType, string $field): bool
    {
        $fieldType = $this->metadata->get([
            'entityDefs', $entityType, 'fields', $field, FieldParam::TYPE
        ]);

        return (bool) $this->metadata->get([
            'fields', $fieldType, 'fullTextSearch'
        ]);
    }

    public function hasFullTextSearch(string $entityType): bool
    {
        return (bool) $this->metadata->get([
            'entityDefs', $entityType, 'collection', 'fullTextSearch'
        ]);
    }

    /**
     * @return string[]
     */
    public function getUseContainsAttributeList(string $entityType): array
    {
        return $this->metadata->get([
            'selectDefs', $entityType, 'textFilterUseContainsAttributeList'
        ]) ?? [];
    }

    /**
     * @return string[]|null
     */
    public function getFullTextSearchColumnList(string $entityType): ?array
    {
        return $this->ormDefs
            ->getEntity($entityType)
            ->getParam('fullTextSearchColumnList');
    }

    public function getRelationType(string $entityType, string $link): string
    {
        return $this->ormDefs
            ->getEntity($entityType)
            ->getRelation($link)
            ->getType();
    }

    public function getAttributeType(string $entityType, string $attribute): string
    {
        return $this->ormDefs
            ->getEntity($entityType)
            ->getAttribute($attribute)
            ->getType();
    }

    public function getFieldType(string $entityType, string $field): ?string
    {
        $entityDefs = $this->ormDefs->getEntity($entityType);

        if (!$entityDefs->hasField($field)) {
            return null;
        }

        return $entityDefs->getField($field)->getType();
    }

    public function getRelationEntityType(string $entityType, string $link): ?string
    {
        $relationDefs = $this->ormDefs
            ->getEntity($entityType)
            ->getRelation($link);

        if (!$relationDefs->hasForeignEntityType()) {
            return null;
        }

        return $relationDefs->getForeignEntityType();
    }

    public function getAttributeRelationParam(string $entityType, string $attribute): ?string
    {
        return $this->ormDefs
            ->getEntity($entityType)
            ->getAttribute($attribute)
            ->getParam(AttributeParam::RELATION);
    }
}
