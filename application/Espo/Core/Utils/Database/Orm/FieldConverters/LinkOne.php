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
use Espo\ORM\Type\AttributeType;

class LinkOne implements FieldConverter
{
    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        return EntityDefs::create()
            ->withAttribute(
                AttributeDefs::create($name . 'Id')
                    ->withType(AttributeType::VARCHAR)
                    ->withNotStorable()
                    ->withParamsMerged([
                        'attributeRole' => 'id',
                        'fieldType' => FieldType::LINK_ONE,
                    ])
            )
            ->withAttribute(
                AttributeDefs::create($name . 'Name')
                    ->withType(AttributeType::VARCHAR)
                    ->withNotStorable()
                    ->withParamsMerged([
                        'attributeRole' => 'name',
                        'fieldType' => FieldType::LINK_ONE,
                    ])
            );
    }
}
