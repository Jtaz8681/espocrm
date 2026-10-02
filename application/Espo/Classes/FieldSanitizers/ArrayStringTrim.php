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

namespace Espo\Classes\FieldSanitizers;

use Espo\Core\FieldSanitize\Sanitizer;
use Espo\Core\FieldSanitize\Sanitizer\Data;

/**
 * @noinspection PhpUnused
 */
class ArrayStringTrim implements Sanitizer
{
    public function sanitize(Data $data, string $field): void
    {
        if (!$data->has($field)) {
            return;
        }

        $value = $data->get($field);

        if (!is_array($value)) {
            return;
        }

        foreach ($value as $i => $item) {
            if (!is_string($item)) {
                continue;
            }

            $value[$i] = trim($item);
        }

        $data->set($field, $value);
    }
}
