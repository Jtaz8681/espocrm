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

namespace Espo\Core\Formula\Functions\DatetimeGroup;

use Espo\Core\Di;

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

class MonthType extends BaseFunction implements Di\DateTimeAware
{
    use Di\DateTimeSetter;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 1) {
            $this->throwTooFewArguments();
        }

        $value = $args[0];

        $timezone = null;
        if (count($args) > 1) {
             $timezone = $args[1];
        }

        if (empty($value)) {
            return 0;
        }

        if (strlen($value) > 11) {
            $resultString = $this->dateTime->convertSystemDateTime($value, $timezone, 'M');
        } else {
            $resultString = $this->dateTime->convertSystemDate($value, 'M');
        }

        return intval($resultString);
    }
}
