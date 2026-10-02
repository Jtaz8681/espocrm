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
use Espo\Core\Utils\Database\Orm\LinkConverter;
use Espo\ORM\Defs\RelationDefs as LinkDefs;
use Espo\ORM\Type\AttributeType;
use LogicException;

class Attachments implements LinkConverter
{
    public function __construct(private HasChildren $hasChildren) {}

    public function convert(LinkDefs $linkDefs, string $entityType): EntityDefs
    {
        $name = $linkDefs->getName();

        $entityDefs = $this->hasChildren->convert($linkDefs, $entityType);

        $entityDefs = $entityDefs->withAttribute(
            AttributeDefs::create($name . 'Types')
                ->withType(AttributeType::JSON_OBJECT)
                ->withNotStorable()
        );

        $relationDefs = $entityDefs->getRelation($name);

        if (!$relationDefs) {
            throw new LogicException();
        }

        $relationDefs = $relationDefs->withConditions([
            'OR' => [
                ['field' => null],
                ['field' => $name],
            ]
        ]);

        return $entityDefs->withRelation($relationDefs);
    }
}
