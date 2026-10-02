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

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs;
use Espo\ORM\Entity;

class VarcharType
{
    private Metadata $metadata;

    private const DEFAULT_MAX_LENGTH = 255;
    private Defs $defs;

    public function __construct(Metadata $metadata, Defs $defs)
    {
        $this->metadata = $metadata;
        $this->defs = $defs;
    }

    public function checkRequired(Entity $entity, string $field): bool
    {
        return $this->isNotEmpty($entity, $field);
    }

    public function checkMaxLength(Entity $entity, string $field, ?int $validationValue): bool
    {
        if (!$this->isNotEmpty($entity, $field)) {
            return true;
        }

        $fieldDefs = $this->defs
            ->getEntity($entity->getEntityType())
            ->getField($field);

        if ($fieldDefs->isNotStorable() && !$validationValue) {
            return true;
        }

        $value = $entity->get($field);

        $maxLength = $validationValue ?? self::DEFAULT_MAX_LENGTH;

        if (mb_strlen($value) > $maxLength) {
            return false;
        }

        return true;
    }

    public function checkPattern(Entity $entity, string $field, ?string $validationValue): bool
    {
        if (!$this->isNotEmpty($entity, $field) || !$validationValue) {
            return true;
        }

        $value = $entity->get($field);
        $pattern = $validationValue;

        if ($validationValue[0] === '$') {
            $patternName = substr($validationValue, 1);

            $pattern = $this->metadata->get(['app', 'regExpPatterns', $patternName, 'pattern']) ??
                $pattern;
        }

        $preparedPattern = '/^' . $pattern . '$/';

        return (bool) preg_match($preparedPattern, $value);
    }

    protected function isNotEmpty(Entity $entity, string $field): bool
    {
        return
            $entity->has($field) &&
            $entity->get($field) !== '' &&
            $entity->get($field) !== null;
    }
}
