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

namespace Espo\Classes\FieldValidators;

use stdClass;

class PasswordType
{
    private const DEFAULT_MAX_LENGTH = 255;

    public function rawCheckValid(stdClass $data, string $field): bool
    {
        $value = $data->$field ?? null;

        if ($value === null) {
            return true;
        }

        return is_string($value);
    }

    public function rawCheckMaxLength(stdClass $data, string $field, ?int $validationValue): bool
    {
        $value = $data->$field ?? null;

        if (!is_string($value)) {
            return true;
        }

        $maxLength = $validationValue ?? self::DEFAULT_MAX_LENGTH;

        if (mb_strlen($value) > $maxLength) {
            return false;
        }

        return true;
    }
}
