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

namespace Espo\Core\FieldValidation;

use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;

use RuntimeException;

class CheckerFactory
{
    /** @var array<string, ?class-string> */
    private $classNameCache = [];

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Whether a field validator can be created.
     */
    public function isCreatable(string $entityType, string $field): bool
    {
        return (bool) $this->getClassName($entityType, $field);
    }

    /**
     * Create a validator (checker).
     *
     * @throws RuntimeException
     */
    public function create(string $entityType, string $field): object
    {
        $className = $this->getClassName($entityType, $field);

        if (!$className) {
            throw new RuntimeException("Validator for '{$entityType}.{$field}' does not exist.");
        }

        return $this->injectableFactory->create($className);
    }

    /**
     * @return ?class-string
     */
    private function getClassName(string $entityType, string $field): ?string
    {
        $key = $entityType . '_' . $field;

        if (!array_key_exists($key, $this->classNameCache)) {
            $this->classNameCache[$key] = $this->getClassNameNoCache($entityType, $field);
        }

        return $this->classNameCache[$key];
    }

    /**
     * @return ?class-string
     */
    private function getClassNameNoCache(string $entityType, string $field): ?string
    {
        $className1 = $this->metadata
            ->get(['entityDefs', $entityType, 'fields', $field, 'validatorClassName']);

        if ($className1) {
            return $className1;
        }

        $fieldType = $this->metadata
            ->get(['entityDefs', $entityType, 'fields', $field, 'type']);

        $className2 = $this->metadata
            ->get(['fields', $fieldType, 'validatorClassName']);

        if ($className2) {
            return $className2;
        }

        $className3 = 'Espo\\Classes\\FieldValidators\\' . ucfirst($fieldType) . 'Type';

        if (class_exists($className3)) {
            return $className3;
        }

        return null;
    }
}
