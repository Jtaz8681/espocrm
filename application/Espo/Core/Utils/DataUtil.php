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

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use stdClass;

class DataUtil
{
    /**
     * @param array<string|int, mixed>|stdClass $data
     * @param array<int, string|string[]>|string $unsetList
     * @return array<string|int, mixed>|stdClass
     */
    public static function unsetByKey(&$data, $unsetList, bool $removeEmptyItems = false)
    {
        if (empty($unsetList)) {
            return $data;
        }

        if (is_string($unsetList)) {
            $unsetList = [$unsetList];
        } else if (!is_array($unsetList)) {
            throw new InvalidArgumentException();
        }

        foreach ($unsetList as $unsetItem) {
            if (is_array($unsetItem)) {
                $path = $unsetItem;
            } else if (is_string($unsetItem)) {
                $path = explode('.', $unsetItem);
            } else {
                throw new LogicException('Bad unset parameter');
            }

            $pointer = &$data;

            $elementArr = [];
            $elementArr[] = &$pointer;

            foreach ($path as $i => $key) {
                if ($i === count($path) - 1) {
                    if (is_array($pointer)) {
                        if (array_key_exists($key, $pointer)) {
                            unset($pointer[$key]);
                        }

                        continue;
                    }

                    if (!is_object($pointer)) {
                        continue;
                    }

                    unset($pointer->$key);

                    if (!$removeEmptyItems) {
                        continue;
                    }

                    for ($j = count($elementArr); $j > 0; $j--) {
                        $pointerBack =& $elementArr[$j];

                        if (is_object($pointerBack) && count(get_object_vars($pointerBack)) === 0) {
                            $previous =& $elementArr[$j - 1];

                            if (is_object($previous)) {
                                $key = $path[$j - 1];
                                unset($previous->$key);
                            }
                        }
                    }

                    continue;
                }

                if (is_array($pointer)) {
                    $pointer = &$pointer[$key];
                } else if (is_object($pointer)) {
                    if (!property_exists($pointer, $key)) {
                        break;
                    }

                    $pointer = &$pointer->$key;
                }

                $elementArr[] = &$pointer;
            }
        }

        return $data;
    }

    /**
     * @param array<string|int, mixed>|stdClass $data
     * @param mixed $needle
     * @return array<string|int, mixed>|stdClass
     */
    public static function unsetByValue(&$data, $needle)
    {
        if (is_object($data)) {
            foreach (get_object_vars($data) as $key => $value) {
                self::unsetByValue($data->$key, $needle);

                if ($data->$key === $needle) {
                    unset($data->$key);
                }
            }
        } else if (is_array($data)) {
            $doReindex = false;

            foreach ($data as $key => $value) {
                self::unsetByValue($data[$key], $needle);

                if ($data[$key] === $needle) {
                    unset($data[$key]);
                    $doReindex = true;
                }
            }

            if ($doReindex) {
                $data = array_values($data);
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed>|stdClass $data
     * @param array<string, mixed>|stdClass $overrideData
     * @return array<string|int, mixed>|stdClass
     */
    public static function merge($data, $overrideData)
    {
        $appendIdentifier = '__APPEND__';

        /** @var mixed $data */
        /** @var mixed $overrideData */

        if (empty($data) && empty($overrideData)) {
            if (is_array($data) || is_array($overrideData)) {
                return [];
            }

            /** @var array<string|int, mixed>|stdClass */
            return $overrideData; /** @phpstan-ignore-line */
        }

        if (is_object($overrideData)) {
            if (empty($data)) {
                $data = (object) [];
            }

            foreach (get_object_vars($overrideData) as $key => $value) {
                if (isset($data->$key)) {
                    $data->$key = self::merge($data->$key, $overrideData->$key);
                } else {
                    $data->$key = $overrideData->$key;
                    self::unsetByValue($data->$key, $appendIdentifier);
                }
            }

            return $data;
        }

        if (is_array($overrideData)) {
            if (empty($data)) {
                $data = [];
            }

            /** @var array<string, mixed> $data */

            if (in_array($appendIdentifier, $overrideData)) {
                foreach ($overrideData as $item) {
                    if ($item === $appendIdentifier) {
                        continue;
                    }

                    $data[] = $item;
                }

                return $data;
            }

            return $overrideData;
        }

        /** @var array<string|int, mixed>|stdClass */
        return $overrideData;
    }

    /**
     * @param string[] $path
     * @since 9.2.0
     * @internal
     */
    public static function setByPath(stdClass $data, array $path, mixed $value): void
    {
        if (count($path) === 1) {
            $property = $path[0];

            $data->$property = $value;

            return;
        }

        if (count($path) < 1) {
            return;
        }

        $property = array_shift($path);

        if (!isset($data->$property)) {
            $data->$property = (object) [];
        }

        if (!($data->$property instanceof stdClass)) {
            throw new RuntimeException("Cannot set by path. Not an object.");
        }

        self::setByPath($data->$property, $path, $value);
    }
}
