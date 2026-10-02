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

namespace Espo\Core\Utils\Database\Orm\LinkConverters;

use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\Core\Utils\Database\Orm\LinkConverter;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class BelongsToParent implements LinkConverter
{
    private const TYPE_LENGTH = 100;

    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();

        $foreignRelationName = $linkDefs->hasForeignRelationName() ?
            $linkDefs->getForeignRelationName() : null;

        $idName = $name . 'Id';
        $nameName = $name . 'Name';
        $typeName = $name . 'Type';

        $relationDefs = RelationDefs::create($name)
            ->withType(RelationType::BELONGS_TO_PARENT)
            ->withKey($idName)
            ->withForeignRelationName($foreignRelationName);

        if ($linkDefs->getParam(RelationParam::DEFERRED_LOAD)) {
            $relationDefs = $relationDefs->withParam(RelationParam::DEFERRED_LOAD, true);
        }

        return EntityDefs::create()
            ->withAttribute(
                AttributeDefs::create($idName)
                    ->withType(AttributeType::FOREIGN_ID)
                    ->withParam('index', $name)
            )
            ->withAttribute(
                AttributeDefs::create($typeName)
                    ->withType(AttributeType::FOREIGN_TYPE)
                    ->withParam(AttributeParam::NOT_NULL, false) // Revise whether needed.
                    ->withParam('index', $name)
                    ->withLength(self::TYPE_LENGTH)
            )
            ->withAttribute(
                AttributeDefs::create($nameName)
                    ->withType(AttributeType::VARCHAR)
                    ->withNotStorable()
            )
            ->withRelation($relationDefs);
    }
}
