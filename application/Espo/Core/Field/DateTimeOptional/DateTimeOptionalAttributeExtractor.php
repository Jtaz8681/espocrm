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

namespace Espo\Core\Field\DateTimeOptional;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\DateTimeOptional;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<DateTimeOptional>
 */
class DateTimeOptionalAttributeExtractor implements AttributeExtractor
{
    /**
     * @param DateTimeOptional $value
     */
    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof DateTimeOptional) {
            throw new InvalidArgumentException();
        }

        if ($value->isAllDay()) {
            return (object) [
                $field . 'Date' => $value->toString(),
                $field => null,
            ];
        }

        return (object) [
            $field => $value->toString(),
            $field . 'Date' => null,
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field => null,
            $field . 'Date' => null,
        ];
    }
}
