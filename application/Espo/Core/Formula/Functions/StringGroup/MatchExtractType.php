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

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;

class MatchExtractType implements Func
{
    /**
     * {@inheritDoc}
     * @return ?string[]
     */
    public function process(EvaluatedArgumentList $arguments): ?array
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $string = $arguments[0];
        $pattern = $arguments[1];

        if (!is_string($string)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($pattern)) {
            throw BadArgumentType::create(2, 'string');
        }

        $result = preg_match($pattern, $string, $matches);

        if (!$result) {
            return null;
        }

        return array_slice($matches, 1);
    }
}
