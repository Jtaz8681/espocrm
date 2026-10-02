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

namespace Espo\Core\Formula\Functions;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\UndefinedKey;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class VariableGetValueByKeyType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $name = $this->evaluate($args[0]);
        $keys = $this->evaluate($args[1]);

        if (!is_string($name)) {
            $this->throwBadArgumentValue(1, 'string');
        }

        if (!property_exists($this->getVariables(), $name)) {
            throw new Error("Cannot access by key of non-existing variable.");
        }

        $reference =& $this->getVariables()->$name;

        $value = null;

        foreach ($keys as $key) {
            $value =& $this->getByKey($reference, $key);

            $reference =& $value;
        }

        return $value;
    }

    /**
     * @throws Error
     */
    private function &getByKey(mixed &$reference, mixed $key): mixed
    {
        if (!is_array($reference) && !$reference instanceof stdClass) {
            throw new Error("Cannot access by key of variable that is non-array and non-object.");
        }

        if (is_array($reference)) {
            if (!is_int($key)) {
                throw new Error("Cannot get array item value by non-integer key.");
            }

            if ($key < 0) {
                throw new UndefinedKey("Cannot get array item value by key that is less than zero.");
            }

            if ($key > count($reference) - 1) {
                throw new UndefinedKey("Cannot get array item value by key that is out of array end.");
            }

            if (!array_key_exists($key, $reference)) {
                throw new UndefinedKey("Cannot get array item value by non-existent key.");
            }

            /** @noinspection PhpUnnecessaryLocalVariableInspection */
            $value =& $reference[$key];

            return $value;
        }

        if (!is_string($key)) {
            throw new Error("Cannot get object item value by non-string key.");
        }

        if ($key === '') {
            throw new Error("Cannot get object item value by empty string key.");
        }

        if (!property_exists($reference, $key)) {
            throw new UndefinedKey("Cannot get object item value by non-existent key.");
        }

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $value =& $reference->$key;

        return $value;
    }
}
