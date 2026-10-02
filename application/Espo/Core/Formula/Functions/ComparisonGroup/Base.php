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

namespace Espo\Core\Formula\Functions\ComparisonGroup;

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

abstract class Base extends BaseFunction
{
    /**
     * @return bool
     * @throws \Espo\Core\Formula\Exceptions\TooFewArguments
     * @throws \Espo\Core\Formula\Exceptions\Error
     */
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $left = $this->evaluate($args[0]);
        $right = $this->evaluate($args[1]);

        return $this->compare($left, $right);
    }

    /**
     * @param mixed $left
     * @param mixed $right
     * @return bool
     */
    abstract protected function compare($left, $right);
}
