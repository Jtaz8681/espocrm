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

class ReplaceType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 3) {
            $this->throwTooFewArguments();
        };

        $string = $args[0];
        $search = $args[1];
        $replace = $args[2];

        if (!is_string($string)) {
            $this->logBadArgumentType(1, 'string');
            return '';
        }

        if (!is_string($search)) {
            $this->logBadArgumentType(2, 'string');
            return $string;
        }

        if (!is_string($replace)) {
            $this->logBadArgumentType(3, 'string');
            return $string;
        }

        return str_replace($search, $replace, $string);
    }
}
