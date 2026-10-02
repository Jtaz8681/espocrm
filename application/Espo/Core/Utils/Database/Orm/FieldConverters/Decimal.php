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

use Doctrine\DBAL\Types\Types;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Type\AttributeType;

class Decimal implements FieldConverter
{
    private const DEFAULT_PRECISION = 13;
    private const DEFAULT_SCALE = 4;

    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $dbType = $fieldDefs->getParam(FieldParam::DB_TYPE) ?? Types::DECIMAL;
        $precision = $fieldDefs->getParam(FieldParam::PRECISION) ?? self::DEFAULT_PRECISION;
        $scale = $fieldDefs->getParam(FieldParam::SCALE) ?? self::DEFAULT_SCALE;
        $notStorable = $fieldDefs->getParam(FieldParam::NOT_STORABLE);

        $defs = AttributeDefs::create($name)
            ->withType(AttributeType::VARCHAR)
            ->withDbType($dbType)
            ->withParam(AttributeParam::PRECISION, $precision)
            ->withParam(AttributeParam::SCALE, $scale);

        if ($notStorable) {
            $defs = $defs->withParam(AttributeParam::NOT_STORABLE, $notStorable);
        }

        return EntityDefs::create()
            ->withAttribute($defs);
    }
}
