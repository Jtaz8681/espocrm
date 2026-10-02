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
use Espo\Core\Utils\Log;
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class HasMany implements LinkConverter
{
    public function __construct(private Log $log) {}

    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();
        $foreignEntityType = $linkDefs->getForeignEntityType();
        $foreignRelationName = $linkDefs->getForeignRelationName();
        $hasField = $linkDefs->getParam('hasField');

        $type = RelationType::HAS_MANY;

        /*$type = $linkDefs->hasRelationshipName() ?
            RelationType::MANY_MANY : // Revise.
            RelationType::HAS_MANY;*/

        if ($linkDefs->hasRelationshipName()) {
            $this->log->warning(
                "Issue with the link '{$name}' in '{$entityType}' entity type. Might be the foreign link " .
                "'{$foreignRelationName}' in '{$foreignEntityType}' entity type is missing. " .
                "Remove the problem link manually.");

            return EntityDefs::create();
        }

        return EntityDefs::create()
            ->withAttribute(
                AttributeDefs::create($name . 'Ids')
                    ->withType(AttributeType::JSON_ARRAY)
                    ->withNotStorable()
                    ->withParam('isLinkStub', !$hasField) // Revise. Change to notExportable?
            )
            ->withAttribute(
                AttributeDefs::create($name . 'Names')
                    ->withType(AttributeType::JSON_OBJECT)
                    ->withNotStorable()
                    ->withParam('isLinkStub', !$hasField)
            )
            ->withRelation(
                RelationDefs::create($name)
                    ->withType($type)
                    ->withForeignEntityType($foreignEntityType)
                    ->withForeignKey($foreignRelationName . 'Id')
                    ->withForeignRelationName($foreignRelationName)
            );
    }
}
