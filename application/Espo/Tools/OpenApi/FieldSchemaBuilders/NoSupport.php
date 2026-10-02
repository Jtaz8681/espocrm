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

use Espo\Tools\OpenApi\FieldSchemaBuilder;
use Espo\Tools\OpenApi\FieldSchemaResult;

class NoSupport implements FieldSchemaBuilder
{
    public function build(string $entityType, string $field): FieldSchemaResult
    {

        return new FieldSchemaResult(
            properties: [],
        );
    }
}
