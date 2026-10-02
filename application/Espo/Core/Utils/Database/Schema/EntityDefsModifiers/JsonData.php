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

namespace Espo\Core\Utils\Database\Schema\EntityDefsModifiers;

use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Schema\EntityDefsModifier;
use Espo\ORM\Defs\EntityDefs as OrmEntityDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Type\AttributeType;

/**
 * A single JSON column instead of multiple field columns.
 */
class JsonData implements EntityDefsModifier
{
    public function modify(OrmEntityDefs $entityDefs): EntityDefs
    {
        $sourceIdAttribute = $entityDefs->getAttribute('id');

        $idAttribute = AttributeDefs::create('id')
            ->withType(AttributeType::ID);

        $length = $sourceIdAttribute->getLength();
        $dbType = $sourceIdAttribute->getParam(AttributeParam::DB_TYPE);

        if ($length) {
            $idAttribute = $idAttribute->withLength($length);
        }

        if ($dbType) {
            $idAttribute = $idAttribute->withDbType($dbType);
        }

        return EntityDefs::create()
            ->withAttribute($idAttribute)
            ->withAttribute(
                AttributeDefs::create('data')
                    ->withType(AttributeType::JSON_OBJECT)
            );
    }
}
