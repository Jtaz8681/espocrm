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

use Espo\Core\ORM\Defs\AttributeParam;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Type\AttributeType;

class LinkMultiple implements FieldConverter
{
    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $idsName = $name . 'Ids';
        $namesName = $name . 'Names';
        $columnsName = $name . 'Columns';

        $idsDefs = AttributeDefs::create($idsName)
            ->withType(AttributeType::JSON_ARRAY)
            ->withNotStorable()
            ->withParamsMerged([
                AttributeParam::IS_LINK_MULTIPLE_ID_LIST => true,
                'relation' => $name,
                'isUnordered' => true,
                'attributeRole' => 'idList',
                'fieldType' => FieldType::LINK_MULTIPLE,
            ]);

        /** @var array<string, mixed> $defaults */
        $defaults = $fieldDefs->getParam('defaultAttributes') ?? [];

        if (array_key_exists($idsName, $defaults)) {
            $idsDefs = $idsDefs->withDefault($defaults[$idsName]);
        }

        $namesDefs = AttributeDefs::create($namesName)
            ->withType(AttributeType::JSON_OBJECT)
            ->withNotStorable()
            ->withParamsMerged([
                AttributeParam::IS_LINK_MULTIPLE_NAME_MAP => true,
                'attributeRole' => 'nameMap',
                'fieldType' => FieldType::LINK_MULTIPLE,
            ]);

        $orderBy = $fieldDefs->getParam('orderBy');
        $orderDirection = $fieldDefs->getParam('orderDirection');

        if ($orderBy) {
            $idsDefs = $idsDefs->withParam('orderBy', $orderBy);

            if ($orderDirection !== null) {
                $idsDefs = $idsDefs->withParam('orderDirection', $orderDirection);
            }
        }

        $columns = $fieldDefs->getParam('columns');

        $columnsDefs = $columns ?
            AttributeDefs::create($columnsName)
                ->withType(AttributeType::JSON_OBJECT)
                ->withNotStorable()
                ->withParamsMerged([
                    'columns' => $columns,
                    'attributeRole' => 'columnsMap',
                ])
            : null;

        $entityDefs = EntityDefs::create()
            ->withAttribute($idsDefs)
            ->withAttribute($namesDefs);

        if ($columnsDefs) {
            $entityDefs = $entityDefs->withAttribute($columnsDefs);
        }

        return $entityDefs;
    }
}
