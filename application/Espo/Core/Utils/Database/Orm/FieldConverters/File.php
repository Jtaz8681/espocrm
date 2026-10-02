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

namespace Espo\Core\Utils\Database\Orm\FieldConverters;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\Entities\Attachment;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class File implements FieldConverter
{
    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $idName = $name . 'Id';
        $nameName = $name . 'Name';

        $idDefs = AttributeDefs::create($idName)
            ->withType(AttributeType::FOREIGN_ID)
            ->withParam('index', false);

        $nameDefs = AttributeDefs::create($nameName)
            ->withType(AttributeType::FOREIGN);

        if ($fieldDefs->isNotStorable()) {
            $idDefs = $idDefs->withNotStorable();

            $nameDefs = $nameDefs->withType(AttributeType::VARCHAR);
        }

        /** @var array<string, mixed> $defaults */
        $defaults = $fieldDefs->getParam('defaultAttributes') ?? [];

        if (array_key_exists($idName, $defaults)) {
            $idDefs = $idDefs->withDefault($defaults[$idName]);
        }

        $relationDefs = null;

        if (!$fieldDefs->isNotStorable()) {
            $nameDefs = $nameDefs->withParamsMerged([
                AttributeParam::RELATION => $name,
                AttributeParam::FOREIGN => Field::NAME,
            ]);

            $relationDefs = RelationDefs::create($name)
                ->withType(RelationType::BELONGS_TO)
                ->withForeignEntityType(Attachment::ENTITY_TYPE)
                ->withKey($idName)
                ->withForeignKey(Attribute::ID)
                ->withParam(RelationParam::FOREIGN, null);
        }

        $entityDefs = EntityDefs::create()
            ->withAttribute($idDefs)
            ->withAttribute($nameDefs);

        if ($relationDefs) {
            $entityDefs = $entityDefs->withRelation($relationDefs);
        }

        return $entityDefs;
    }
}
