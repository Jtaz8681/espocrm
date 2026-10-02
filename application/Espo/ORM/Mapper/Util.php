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

namespace Espo\ORM\Mapper;

use Espo\ORM\Entity;
use stdClass;

/**
 * @since 10.0.0
 * @internal
 */
class Util
{
    /**
     * @internal
     */
    public static function prepareValue(?string $type, mixed $value): mixed
    {
        if ($type == Entity::JSON_ARRAY && is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else if ($type == Entity::JSON_OBJECT && (is_array($value) || $value instanceof stdClass)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            if (is_array($value) || is_object($value)) {
                return null;
            }
        }

        return $value;
    }
}
