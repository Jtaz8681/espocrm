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

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

class PushType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $list = $this->evaluate($args[0]) ?? [];

        if (!is_array($list)) {
            $this->throwError("Argument is non-array.");
        }

        foreach ($args as $i => $v) {
            if ($i === 0) {
                continue;
            }

            $element = $this->evaluate($args[$i]);

            $list[] = $element;
        }

        return $list;
    }
}
