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

namespace Espo\Classes\FieldValidators;

use Espo\Core\Currency\CalculatorUtil;
use Espo\ORM\Entity;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class DecimalType
{
    public function __construct()
    {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        return $this->isNotEmpty($entity, $field);
    }

    /**
     * @param mixed $validationValue
     * @noinspection PhpUnused
     */
    public function checkMax(Entity $entity, string $field, $validationValue): bool
    {
        if (!$this->isNotEmpty($entity, $field)) {
            return true;
        }

        return CalculatorUtil::compare($entity->get($field), $validationValue) <= 0;
    }

    /**
     * @param mixed $validationValue
     * @noinspection PhpUnused
     */
    public function checkMin(Entity $entity, string $field, $validationValue): bool
    {
        if (!$this->isNotEmpty($entity, $field)) {
            return true;
        }

        return CalculatorUtil::compare($entity->get($field), $validationValue) >= 0;
    }

    /** @noinspection PhpUnused */
    public function rawCheckValid(stdClass $data, string $field): bool
    {
        $value = $data->$field ?? null;

        if ($value === null) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        if ($value === '') {
            return false;
        }

        if (preg_match('/^-?[0-9]+\.?[0-9]*$/', $value)) {
            return true;
        }

        return false;
    }

    protected function isNotEmpty(Entity $entity, string $field): bool
    {
        return $entity->has($field) && $entity->get($field) !== null;
    }
}
