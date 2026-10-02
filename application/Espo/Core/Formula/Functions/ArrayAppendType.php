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

namespace Espo\Core\Formula\Functions;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\Error;

/**
 * @noinspection PhpUnused
 */
class ArrayAppendType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $name = $this->evaluate($args[0]);

        if (!is_string($name)) {
            $this->throwBadArgumentValue(1, 'string');
        }

        $value = $this->evaluate($args[1]);

        if (!property_exists($this->getVariables(), $name)) {
            throw new Error("Cannot array-append to not existing variable.");
        }

        $array =& $this->getVariables()->$name;

        if (!is_array($array)) {
            throw new Error("Cannot array-append to non-array variable.");
        }

        $array[] = $value;
    }
}
