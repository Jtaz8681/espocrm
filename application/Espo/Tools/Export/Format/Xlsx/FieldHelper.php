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

namespace Espo\Tools\Export\Format\Xlsx;

use Espo\Core\ORM\Type\FieldType;
use Espo\ORM\Defs;

class FieldHelper
{
    public function __construct(
        private Defs $ormDefs
    ) {}

    public function isForeignReference(string $name): bool
    {
        return str_contains($name, '_');
    }

    private function isForeign(string $entityType, string $name): bool
    {
        if ($this->isForeignReference($name)) {
            return true;
        }

        $entityDefs = $this->ormDefs->getEntity($entityType);

        return
            $entityDefs->hasField($name) &&
            $entityDefs->getField($name)->getType() === FieldType::FOREIGN;
    }

    public function getData(string $entityType, string $name): ?FieldData
    {
        $entityDefs = $this->ormDefs->getEntity($entityType);

        if (!$this->isForeign($entityType, $name)) {
            if (!$entityDefs->hasField($name)) {
                return null;
            }

            $type = $entityDefs
                ->getField($name)
                ->getType();

            return new FieldData($entityType, $name, $type, null);
        }

        $link = null;
        $field = null;

        if (
            $entityDefs->hasField($name) &&
            $entityDefs->getField($name)->getType() === FieldType::FOREIGN
        ) {
            $fieldDefs = $entityDefs->getField($name);

            $link = $fieldDefs->getParam('link');
            $field = $fieldDefs->getParam('field');
        } else if (str_contains($name, '_')) {
            [$link, $field] = explode('_', $name);
        }

        if (!$link || !$field) {
            return null;
        }

        $entityDefs = $this->ormDefs->getEntity($entityType);

        if (!$entityDefs->hasRelation($link)) {
            return null;
        }

        $relationDefs = $entityDefs->getRelation($link);

        if (!$relationDefs->hasForeignEntityType()) {
            return null;
        }

        $foreignEntityType = $relationDefs->getForeignEntityType();

        $type = $this->ormDefs
            ->getEntity($foreignEntityType)
            ->getField($field)
            ->getType();

        return new FieldData($foreignEntityType, $field, $type, $link);
    }
}
