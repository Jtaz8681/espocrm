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
use Espo\Entities\PhoneNumber;
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class SmsPhoneNumber implements LinkConverter
{
    public function __construct() {}

    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();
        $foreignEntityType = PhoneNumber::ENTITY_TYPE;

        $key1 = lcfirst($entityType) . 'Id';
        $key2 = lcfirst($foreignEntityType) . 'Id';

        $relationDefs = RelationDefs::create($name)
            ->withType(RelationType::MANY_MANY)
            ->withForeignEntityType($foreignEntityType)
            ->withKey('id')
            ->withForeignKey('id')
            ->withMidKeys($key1, $key2);

        return EntityDefs::create()
            ->withAttribute(
                AttributeDefs::create($name . 'Ids')
                    ->withType(AttributeType::JSON_ARRAY)
                    ->withNotStorable()
            )
            ->withAttribute(
                AttributeDefs::create($name . 'Names')
                    ->withType(AttributeType::JSON_OBJECT)
                    ->withNotStorable()
            )
            ->withRelation($relationDefs);
    }
}
