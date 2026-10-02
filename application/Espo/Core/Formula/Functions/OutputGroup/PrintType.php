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

namespace Espo\Core\Formula\Functions\OutputGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;

use stdClass;
use const JSON_UNESCAPED_UNICODE;

class PrintType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) === 0) {
            $this->throwTooFewArguments(1);
        }

        $value = $this->evaluate($args[0]);

        if (is_int($value) || is_float($value)) {
            $value = strval($value);
        } else if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } else if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else if ($value instanceof stdClass) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else if ($value === null) {
            $value = 'null';
        }

        $variables = $this->getVariables();

        if (!isset($variables->__output)) {
            $variables->__output = '';
        }

        $variables->__output = $variables->__output .= $value;
    }
}
