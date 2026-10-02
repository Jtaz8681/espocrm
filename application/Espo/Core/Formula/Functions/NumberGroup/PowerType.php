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

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;

/**
 * @noinspection PhpUnused
 */
class PowerType implements Func
{
    public function process(EvaluatedArgumentList $arguments): float|int
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $value = $arguments[0];
        $exp = $arguments[1];

        if (!is_int($value) && !is_float($value)) {
            throw BadArgumentType::create(1, 'int|float');
        }

        if (!is_int($exp) && !is_float($exp)) {
            throw BadArgumentType::create(2, 'int|float');
        }

        return pow($value, $exp);
    }
}
