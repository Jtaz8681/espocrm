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

class AbsType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments();
        }

        $value = $this->evaluate($args[0]);

        if (!is_numeric($value)) {
            return null;
        }

        if (is_string($value)) {
            if (str_contains($value, '.')) {
                $value = (float) $value;
            } else {
                $value = (int) $value;
            }
        }

        return abs($value);
    }
}
