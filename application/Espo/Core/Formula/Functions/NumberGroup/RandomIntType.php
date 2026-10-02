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

class RandomIntType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        $min = $args[0] ?? 0;
        $max = $args[1] ?? PHP_INT_MAX;

        if (!is_int($min)) {
            $this->throwBadArgumentType(1, 'int');
        }

        if (!is_int($max)) {
            $this->throwBadArgumentType(2, 'int');
        }

        return random_int($min, $max);
    }
}
