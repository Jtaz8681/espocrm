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
use Espo\Core\Utils\Metadata;

/**
 * @since 9.0.3
 */
class InternationalValidator
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function isValid(string $number, bool $allowExtension = false): bool
    {
        if ($number === '') {
            return false;
        }

        $pattern = $this->metadata->get(['app', 'regExpPatterns', 'phoneNumberLoose', 'pattern']);

        if (!$pattern) {
            return true;
        }

        $preparedPattern = '/^' . $pattern . '$/';

        if (!preg_match($preparedPattern, $number)) {
            return false;
        }

        $ext = null;

        if ($allowExtension) {
            [$number, $ext] = Util::splitExtension($number);
        }

        if ($ext) {
            if (!preg_match('/[0-9]+/', $ext)) {
                return false;
            }

            if (strlen($ext) > 6) {
                return false;
            }
        }

        try {
            $numberObj = PhoneNumber::parse($number);
        } catch (PhoneNumberParseException) {
            return false;
        }

        if ((string) $numberObj !== $number) {
            return false;
        }

        return $numberObj->isPossibleNumber();
    }
}
