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

namespace Espo\Tools\OpenApi\FieldSchemaBuilders;

use Espo\Tools\OpenApi\Type;
use Doctrine\DBAL\Types\Types;
use Espo\ORM\Defs;
use Espo\Tools\OpenApi\FieldSchemaBuilder;
use Espo\Tools\OpenApi\FieldSchemaResult;

class IdType implements FieldSchemaBuilder
{
    public function __construct(
        private Defs $defs,
    ) {}

    public function build(string $entityType, string $field): FieldSchemaResult
    {
        $fieldDefs = $this->defs->getEntity($entityType)->tryGetField($field);

        $schema = (object) [];
        $schema->type = Type::STRING;
        $schema->readOnly = true;

        $dbType = $fieldDefs?->getParam(Defs\Params\FieldParam::DB_TYPE);

        if ($dbType === Types::BIGINT || $dbType === Types::INTEGER) {
            $schema->type = Type::INTEGER;
        }

        $schema->description = 'An ID.';

        return new FieldSchemaResult(
            properties: [
                $field => get_object_vars($schema),
            ],
        );
    }
}

