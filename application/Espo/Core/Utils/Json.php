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

namespace Espo\Core\Utils;

use JsonException;

use const JSON_THROW_ON_ERROR;

class Json
{
    /**
     * JSON encode.
     *
     * @param mixed $value
     * @throws JsonException
     */
    public static function encode($value, int $options = 0): string
    {
        return json_encode($value, $options | JSON_THROW_ON_ERROR);
    }

    /**
     * JSON decode.
     *
     * @param bool $associative Objects will be converted to associative.
     * @return mixed
     * @throws JsonException
     */
    public static function decode(string $json, bool $associative = false)
    {
        return json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
    }
}
