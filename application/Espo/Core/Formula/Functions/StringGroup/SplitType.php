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

use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\ArgumentList;

class SplitType extends BaseFunction
{
    /**
     * @return string[]
     * @throws \Espo\Core\Formula\Exceptions\TooFewArguments
     * @throws \Espo\Core\Formula\Exceptions\BadArgumentType
     * @throws \Espo\Core\Formula\Exceptions\Error
     */
    public function process(ArgumentList $args)
    {
        $evaluatedArgs = $this->evaluate($args);

        if (count($evaluatedArgs) < 2) {
            $this->throwTooFewArguments(2);
        }

        $string = $evaluatedArgs[0] ?? '';
        $separator = $evaluatedArgs[1];

        if (!is_string($string)) {
            $this->throwBadArgumentType(1, 'string');
        }

        if (!is_string($separator)) {
            $this->throwBadArgumentType(2, 'string');
        }

        if ($separator === '') {
            return mb_str_split($string);
        }

        return explode($separator, $string);
    }
}
