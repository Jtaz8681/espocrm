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

namespace Espo\Core\Formula\Functions\ArrayGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Formula\Functions\BaseFunction;

class AtType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $array = $args[0] ?? [];
        $index = $args[1];

        if (!is_array($array)) {
            $this->throwBadArgumentType(1, 'array');
        }

        if (!is_int($index)) {
            $this->throwBadArgumentType(2, 'int');
        }

        if (!array_key_exists($index, $array)) {
            throw new FunctionRuntimeError("Cannot access array value by non-existing index $index.");
        }

        return $array[$index];
    }
}
