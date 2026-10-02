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

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\FuncVariablesAware;
use Espo\Core\Formula\Variables;

class VariableType implements FuncVariablesAware
{
    public function process(EvaluatedArgumentList $arguments, Variables $variables): mixed
    {
        if (!count($arguments)) {
            throw new Error("No variable name.");
        }

        $name = $arguments[0];

        if (!is_string($name)) {
            throw new Error("Bad variable name.");
        }

        if ($name === '') {
            throw new Error("Empty variable name.");
        }

        return $variables->tryGet($name);
    }
}
