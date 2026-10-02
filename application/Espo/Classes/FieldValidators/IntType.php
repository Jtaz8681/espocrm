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

use Doctrine\DBAL\Types\Types;
use Espo\ORM\Defs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Entity;
use stdClass;

class IntType
{
    public function __construct(
        private Defs $defs,
    ) {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        return $this->isNotEmpty($entity, $field);
    }

    /** @noinspection PhpUnused */
    public function checkRangeInternal(Entity $entity, string $field): bool
    {
        $value = $entity->get($field);

        if ($value === null) {
            return true;
        }

        $dbType = $this->defs
            ->getEntity($entity->getEntityType())
            ->tryGetAttribute($field)
            ?->getParam(AttributeParam::DB_TYPE) ?? Types::INTEGER;

        $ranges = [
            Types::INTEGER => [-2147483648, 2147483647],
            Types::SMALLINT => [-32768, 32767],
        ];

        $range = $ranges[$dbType] ?? null;

        if (!$range) {
            return true;
        }

        if ($value < $range[0] || $value > $range[1]) {
            return false;
        }

        return true;
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

        if ($entity->get($field) > $validationValue) {
            return false;
        }

        return true;
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

        if ($entity->get($field) < $validationValue) {
            return false;
        }

        return true;
    }

    /** @noinspection PhpUnused */
    public function rawCheckValid(stdClass $data, string $field): bool
    {
        if (!isset($data->$field)) {
            return true;
        }

        $value = $data->$field;

        if ($value === '') {
            return true;
        }

        if (is_numeric($value)) {
            return true;
        }

        return false;
    }

    protected function isNotEmpty(Entity $entity, string $field): bool
    {
        return $entity->has($field) && $entity->get($field) !== null;
    }
}
