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

namespace Espo\Core\Record\Duplicator;

use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\RelationDefs;
use Espo\ORM\Entity;
use Espo\ORM\Defs;
use Espo\ORM\Defs\FieldDefs;
use Espo\Core\Utils\FieldUtil;
use Espo\ORM\Type\RelationType;
use stdClass;

/**
 * Duplicates an entity.
 */
class EntityDuplicator
{
    public function __construct(
        private Defs $defs,
        private FieldDuplicatorFactory $fieldDuplicatorFactory,
        private FieldUtil $fieldUtil,
        private Metadata $metadata,
    ) {}

    public function duplicate(Entity $entity): stdClass
    {
        $entityType = $entity->getEntityType();
        $valueMap = $entity->getValueMap();

        unset($valueMap->id);

        $entityDefs = $this->defs->getEntity($entityType);

        foreach ($entityDefs->getFieldList() as $fieldDefs) {
            $this->processIgnoreField($entity, $fieldDefs, $valueMap);
        }

        foreach ($entityDefs->getFieldList() as $fieldDefs) {
            $this->processField($entity, $fieldDefs, $valueMap);
        }

        foreach ($entityDefs->getRelationList() as $relationDefs) {
            $this->processLink($entity, $relationDefs, $entityDefs, $valueMap);
        }

        return $valueMap;
    }

    private function processIgnoreField(Entity $entity, FieldDefs $fieldDefs, stdClass $valueMap): void
    {
        $entityType = $entity->getEntityType();
        $field = $fieldDefs->getName();

        if ($this->toIgnoreField($entityType, $fieldDefs)) {
            $attributeList = $this->fieldUtil->getAttributeList($entityType, $field);

            foreach ($attributeList as $attribute) {
                unset($valueMap->$attribute);
            }
        }
    }

    private function processField(Entity $entity, FieldDefs $fieldDefs, stdClass $valueMap): void
    {
        $entityType = $entity->getEntityType();
        $field = $fieldDefs->getName();

        if ($this->toIgnoreField($entityType, $fieldDefs)) {
            return;
        }

        if (!$this->fieldDuplicatorFactory->has($entityType, $field)) {
            return;
        }

        $fieldDuplicator = $this->fieldDuplicatorFactory->create($entityType, $field);

        $fieldValueMap = $fieldDuplicator->duplicate($entity, $field);

        foreach (get_object_vars($fieldValueMap) as $attribute => $value) {
            $valueMap->$attribute = $value;
        }
    }

    private function processLink(
        Entity $entity,
        RelationDefs $relationDefs,
        Defs\EntityDefs $entityDefs,
        stdClass $valueMap,
    ): void {

        $link = $relationDefs->getName();

        if (
            !in_array($relationDefs->getType(), [
                RelationType::BELONGS_TO,
                RelationType::BELONGS_TO_PARENT,
                RelationType::HAS_ONE,
            ])
        ) {
            return;
        }

        if ($entityDefs->hasField($link)) {
            return;
        }

        unset($valueMap->{$link . 'Id'});
        unset($valueMap->{$link . 'Name'});

        if ($relationDefs->getType() === RelationType::BELONGS_TO_PARENT) {
            unset($valueMap->{$link . 'Type'});
        }
    }

    private function toIgnoreField(string $entityType, FieldDefs $fieldDefs): bool
    {
        $type = $fieldDefs->getType();

        if (
            in_array($type, [
                FieldType::AUTOINCREMENT,
                FieldType::NUMBER,
                FieldType::LINK_ONE,
                // Additional measure. It's not fetched.
                FieldType::PASSWORD,
            ])
        ) {
            return true;
        }

        if ($this->metadata->get(['scopes', $entityType, 'statusField']) === $fieldDefs->getName()) {
            return true;
        }

        return (bool) $fieldDefs->getParam('duplicateIgnore');
    }
}
