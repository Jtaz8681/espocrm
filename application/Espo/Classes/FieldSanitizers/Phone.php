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
use Espo\Core\PhoneNumber\Sanitizer as PhoneNumberSanitizer;
use stdClass;

class Phone implements Sanitizer
{
    public function __construct(
        private PhoneNumberSanitizer $phoneNumberSanitizer
    ) {}

    public function sanitize(Data $data, string $field): void
    {
        $number = $data->get($field);

        if ($number !== null) {
            $number = $this->phoneNumberSanitizer->sanitize($number);

            $data->set($field, $number);
        }

        $items = $data->get($field . 'Data');

        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if (!$item instanceof stdClass) {
                continue;
            }

            $number = $item->phoneNumber ?? null;

            if (!is_scalar($number)) {
                continue;
            }

            $number = (string) $number;

            $item->phoneNumber = $this->phoneNumberSanitizer->sanitize($number);
        }

        $data->set($field . 'Data', $items);
    }
}
