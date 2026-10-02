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

namespace Espo\Core\Formula\Functions\StringGroup;

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

class MatchType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $string = $args[0];
        $regexp = $args[1];

        if (!is_string($string)) {
            return null;
        }

        if (!is_string($regexp)) {
            return null;
        }

        $offset = $args[2] ?? 0;

        $result = preg_match($regexp, $string, $matches, 0, $offset);

        if (!$result) {
            return null;
        }

        if (!count($matches)) {
            return null;
        }

        return $matches[0];
    }
}
