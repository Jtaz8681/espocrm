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

namespace Espo\ORM;

use Espo\ORM\Type\AttributeType;

/**
 * @internal
 */
class Util
{
    /**
     * @internal
     */
    public static function areValuesEqual(string $type, mixed $v1, mixed $v2, bool $isUnordered = false): bool
    {
        $stack = [[$type, $v1, $v2, $isUnordered]];

        while ($stack) {
            [$type, $v1, $v2, $isUnordered] = array_pop($stack);

            if ($v1 === $v2) {
                continue;
            }

            if ($type === AttributeType::JSON_ARRAY) {
                if (is_array($v1) && is_array($v2)) {
                    if ($isUnordered) {
                        sort($v1);
                        sort($v2);
                    }

                    if ($v1 != $v2) {
                        return false;
                    }

                    foreach ($v1 as $i => $itemValue) {
                        $otherValue = $v2[$i];

                        if (is_object($itemValue) && is_object($otherValue)) {
                            $stack[] = [AttributeType::JSON_OBJECT, $itemValue, $otherValue, false];

                            continue;
                        }

                        if (is_array($itemValue) && is_array($otherValue)) {
                            $stack[] = [AttributeType::JSON_ARRAY, $itemValue, $otherValue, false];

                            continue;
                        }

                        if ($itemValue !== $otherValue) {
                            return false;
                        }
                    }

                    continue;
                }

                return false;
            }

            if ($type === AttributeType::JSON_OBJECT) {
                if (is_object($v1) && is_object($v2)) {
                    if ($v1 != $v2) {
                        return false;
                    }

                    $a1 = get_object_vars($v1);
                    $a2 = get_object_vars($v2);

                    foreach (get_object_vars($v1) as $key => $itemValue) {
                        if (is_object($a1[$key]) && is_object($a2[$key])) {
                            $stack[] = [AttributeType::JSON_OBJECT, $a1[$key], $a2[$key], false];

                            continue;
                        }

                        if (is_array($a1[$key]) && is_array($a2[$key])) {
                            $stack[] = [AttributeType::JSON_ARRAY, $a1[$key], $a2[$key], false];

                            continue;
                        }

                        if ($a1[$key] !== $a2[$key]) {
                            return false;
                        }
                    }

                    continue;
                }
            }

            return false;
        }

        return true;
    }
}
