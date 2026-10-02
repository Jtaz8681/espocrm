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

class PadType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $input = $args[0];
        $length = $args[1];
        $string = $args[2] ?? ' ';
        $type = $args[3] ?? 'right';

        if (!is_string($input)) {
            $input = strval($input);
        }

        if (!is_int($length)) {
            $this->throwBadArgumentType(2);
        }

        $map = [
            'right' => \STR_PAD_RIGHT,
            'left' => \STR_PAD_LEFT,
            'both' => \STR_PAD_BOTH,
        ];

        $padType = $map[$type] ?? \STR_PAD_RIGHT;

        return mb_str_pad($input, $length, $string, $padType);
    }
}
