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

use Espo\Core\Formula\Exceptions\UndefinedKey;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\ArgumentList;

class NullCoalescingType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $array = [];

        foreach ($args as $arg) {
            $array[] = $arg;
        }

        foreach (array_slice($array, 0, -1) as $arg) {
            try {
                $value = $this->evaluate($arg);
            } catch (UndefinedKey $e) {
                if ($e->getLevelsRisen() > 1) {
                    throw $e;
                }

                $value = null;
            }


            if ($value !== null) {
                return $value;
            }
        }

        return $this->evaluate($array[count($array) - 1]);
    }
}
