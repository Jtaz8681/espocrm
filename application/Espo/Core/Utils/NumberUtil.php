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

class NumberUtil
{
    public function __construct(
        private ?string $decimalMark = '.',
        private ?string $thousandSeparator = ','
    ) {}

    /**
     * @param scalar $value
     */
    public function format(
        $value,
        ?int $decimals = null,
        ?string $decimalMark = null,
        ?string $thousandSeparator = null
    ): string {

        if (is_null($decimalMark)) {
            $decimalMark = $this->decimalMark;
        }

        if (is_null($thousandSeparator)) {
            $thousandSeparator = $this->thousandSeparator;
        }

        if (!is_null($decimals)) {
            return number_format((float) $value, $decimals, $decimalMark, $thousandSeparator);
        }

        $arr = explode('.', strval($value));

        $r = '0';

        if (!empty($arr[0])) {
            $r = number_format(intval($arr[0]), 0, '.', $thousandSeparator);
        }

        if (!empty($arr[1])) {
            $r = $r . $decimalMark . $arr[1];
        }

        return $r;
    }
}
