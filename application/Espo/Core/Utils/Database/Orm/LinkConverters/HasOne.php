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
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class HasOne implements LinkConverter
{
    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();
        $foreignEntityType = $linkDefs->getForeignEntityType();
        $foreignRelationName = $linkDefs->hasForeignRelationName() ? $linkDefs->getForeignRelationName() : null;
        $noForeignName = $linkDefs->getParam('noForeignName');
        $foreignName = $linkDefs->getParam('foreignName') ?? 'name';
        $noJoin = $linkDefs->getParam('noJoin');

        $idName = $name . 'Id';
        $nameName = $name . 'Name';

        $idAttributeDefs = AttributeDefs::create($idName)
            ->withType($noJoin ? AttributeType::VARCHAR : AttributeType::FOREIGN)
            ->withNotStorable()
            ->withParam(AttributeParam::RELATION, $name)
            ->withParam(AttributeParam::FOREIGN, Attribute::ID);

        $nameAttributeDefs = !$noForeignName ?
            (
            AttributeDefs::create($nameName)
                ->withType($noJoin ? AttributeType::VARCHAR : AttributeType::FOREIGN)
                ->withNotStorable()
                ->withParam(AttributeParam::RELATION, $name)
                ->withParam(AttributeParam::FOREIGN, $foreignName)
            ) : null;

        $relationDefs = RelationDefs::create($name)
            ->withType(RelationType::HAS_ONE)
            ->withForeignEntityType($foreignEntityType);

        if ($foreignRelationName) {
            $relationDefs = $relationDefs
                ->withForeignKey($foreignRelationName . 'Id')
                ->withForeignRelationName($foreignRelationName);
        }

        $entityDefs = EntityDefs::create()
            ->withAttribute($idAttributeDefs)
            ->withRelation($relationDefs);

        if ($nameAttributeDefs) {
            $entityDefs = $entityDefs->withAttribute($nameAttributeDefs);
        }

        return $entityDefs;
    }
}
