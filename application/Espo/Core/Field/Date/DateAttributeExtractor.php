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

namespace Espo\Core\Field\Date;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\Date;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<Date>
 */
class DateAttributeExtractor implements AttributeExtractor
{
    /**
     * @param Date $value
     */
    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof Date) {
            throw new InvalidArgumentException();
        }

        return (object) [
            $field => $value->toString(),
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field => null,
        ];
    }
}
