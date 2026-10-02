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

namespace Espo\Core\Utils;

use stdClass;

class ObjectUtil
{
    /**
     * Deep clone.
     */
    public static function clone(stdClass $source): stdClass
    {
        $cloned = (object) [];

        foreach (get_object_vars($source) as $k => $v) {
            $cloned->$k = self::cloneItem($v);
        }

        return $cloned;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    private static function cloneItem($item)
    {
        if (is_array($item)) {
            $cloned = [];

            foreach ($item as $i => $v) {
                $cloned[$i] = self::cloneItem($v);
            }

            return $cloned;
        }

        if ($item instanceof stdClass) {
            return self::clone($item);
        }

        if (is_object($item)) {
            return clone $item;
        }

        return $item;
    }
}
