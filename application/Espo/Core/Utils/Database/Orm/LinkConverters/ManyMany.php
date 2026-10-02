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
use Espo\Core\Utils\Util;
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class ManyMany implements LinkConverter
{
    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();
        $foreignEntityType = $linkDefs->getForeignEntityType();
        $foreignRelationName = $linkDefs->getForeignRelationName();
        $hasField = $linkDefs->getParam('hasField');
        $columnAttributeMap = $linkDefs->getParam('columnAttributeMap');

        $relationshipName = $linkDefs->hasRelationshipName() ?
            $linkDefs->getRelationshipName() :
            self::composeRelationshipName($entityType, $foreignEntityType);

        if ($linkDefs->hasMidKey() && $linkDefs->hasForeignMidKey()) {
            $key1 = $linkDefs->getMidKey();
            $key2 = $linkDefs->getForeignMidKey();
        } else {
            $key1 = lcfirst($entityType) . 'Id';
            $key2 = lcfirst($foreignEntityType) . 'Id';

            if ($key1 === $key2) {
                [$key1, $key2] = strcmp($name, $foreignRelationName) > 0 ?
                    ['leftId', 'rightId'] :
                    ['rightId', 'leftId'];
            }
        }

        $relationDefs = RelationDefs::create($name)
            ->withType(RelationType::MANY_MANY)
            ->withForeignEntityType($foreignEntityType)
            ->withRelationshipName($relationshipName)
            ->withKey(Attribute::ID)
            ->withForeignKey(Attribute::ID)
            ->withMidKeys($key1, $key2)
            ->withForeignRelationName($foreignRelationName);

        if ($columnAttributeMap) {
            $relationDefs = $relationDefs->withParam('columnAttributeMap', $columnAttributeMap);
        }

        return EntityDefs::create()
            ->withAttribute(
                AttributeDefs::create($name . 'Ids')
                    ->withType(AttributeType::JSON_ARRAY)
                    ->withNotStorable()
                    ->withParam('isLinkStub', !$hasField) // Revise.
            )
            ->withAttribute(
                AttributeDefs::create($name . 'Names')
                    ->withType(AttributeType::JSON_OBJECT)
                    ->withNotStorable()
                    ->withParam('isLinkStub', !$hasField) // Revise.
            )
            ->withRelation($relationDefs);
    }

    private static function composeRelationshipName(string $left, string $right): string
    {
        $parts = [
            Util::toCamelCase(lcfirst($left)),
            Util::toCamelCase(lcfirst($right)),
        ];

        sort($parts);

        return Util::toCamelCase(implode('_', $parts));
    }
}
