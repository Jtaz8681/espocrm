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

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Espo\Core\Utils\Config;

class Sanitizer
{
    public function __construct(
        private Config $config
    ) {}

    public function sanitize(string $value, ?string $countryCode = null): string
    {
        $value = trim($value);

        if (str_starts_with($value, '+')) {
            if ($this->config->get('phoneNumberInternational')) {
                return $this->parsePhoneNumber($value, null);
            }

            return $value;
        }

        if (!$countryCode) {
            return $value;
        }

        $code = strtoupper($countryCode);

        return $this->parsePhoneNumber($value, $code);
    }

    private function parsePhoneNumber(string $value, ?string $countryCode): string
    {
        $ext = null;

        if ($this->config->get('phoneNumberExtensions')) {
            [$value, $ext] = Util::splitExtension($value);
        }

        try {
            $number = PhoneNumber::parse($value, $countryCode);
        } catch (PhoneNumberParseException) {
            return $value;
        }

        $output = (string) $number;

        if ($ext) {
            $output .= ' ext. ' . $ext;
        }

        return $output;
    }
}
