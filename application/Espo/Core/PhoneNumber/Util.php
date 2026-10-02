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

namespace Espo\Core\PhoneNumber;

class Util
{
    /**
     * @internal Do not use in custom code.
     * @return array{string, ?string}
     */
    public static function splitExtension(string $value): array
    {
        $ext = null;

        $delimiters = [
            'ext.',
            'x.',
            'x',
            '#',
        ];

        foreach ($delimiters as $delimiter) {
            $index = strrpos($value, $delimiter);

            if ($index === false || $index < 2) {
                continue;
            }

            $ext = trim(substr($value, $index + strlen($delimiter)));
            $value = trim(substr($value, 0, $index));

            break;
        }

        return [$value, $ext];
    }
}
