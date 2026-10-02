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

use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Type\AttributeType;

class LinkParent implements FieldConverter
{
    private const TYPE_LENGTH = 100;

    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $idName = $name . 'Id';
        $typeName = $name . 'Type';
        $nameName = $name . 'Name';

        $idDefs = AttributeDefs::create($idName)
            ->withType(AttributeType::FOREIGN_ID)
            ->withParamsMerged([
                'index' => $name,
                'attributeRole' => 'id',
                'fieldType' => FieldType::LINK_PARENT,
            ]);

        $typeDefs = AttributeDefs::create($typeName)
            ->withType(AttributeType::FOREIGN_TYPE)
            ->withParam(AttributeParam::NOT_NULL, false)
            ->withParam('index', $name)
            ->withLength(self::TYPE_LENGTH)
            ->withParamsMerged([
                'attributeRole' => 'type',
                'fieldType' => FieldType::LINK_PARENT,
            ]);

        $nameDefs = AttributeDefs::create($nameName)
            ->withType(AttributeType::VARCHAR)
            ->withNotStorable()
            ->withParamsMerged([
                AttributeParam::RELATION => $name,
                'isParentName' => true,
                'attributeRole' => 'name',
                'fieldType' => FieldType::LINK_PARENT,
            ]);

        if ($fieldDefs->isNotStorable()) {
            $idDefs = $idDefs->withNotStorable();
            $typeDefs = $typeDefs->withNotStorable();
        }

        /** @var array<string, mixed> $defaults */
        $defaults = $fieldDefs->getParam('defaultAttributes') ?? [];

        if (array_key_exists($idName, $defaults)) {
            $idDefs = $idDefs->withDefault($defaults[$idName]);
        }

        if (array_key_exists($typeName, $defaults)) {
            $typeDefs = $idDefs->withDefault($defaults[$typeName]);
        }

        return EntityDefs::create()
            ->withAttribute($idDefs)
            ->withAttribute($typeDefs)
            ->withAttribute($nameDefs);
    }
}
