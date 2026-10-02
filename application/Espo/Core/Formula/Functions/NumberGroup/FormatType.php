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

namespace Espo\Core\Formula\Functions\NumberGroup;

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

use Espo\Core\Di;

class FormatType extends BaseFunction implements
    Di\NumberAware
{
    use Di\NumberSetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments();
        }

        $decimals = null;
        if (count($args) > 1) {
            $decimals = $this->evaluate($args[1]);
        }

        $decimalMark = null;
        if (count($args) > 2) {
            $decimalMark = $this->evaluate($args[2]);
        }

        $thousandSeparator = null;
        if (count($args) > 3) {
            $thousandSeparator = $this->evaluate($args[3]);
        }

        $value = $this->evaluate($args[0]);

        return $this->number->format($value, $decimals, $decimalMark, $thousandSeparator);
    }
}
