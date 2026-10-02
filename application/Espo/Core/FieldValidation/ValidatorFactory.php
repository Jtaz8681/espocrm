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

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\FieldUtil;

use Espo\ORM\Entity;
use RuntimeException;

class ValidatorFactory
{

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private FieldUtil $fieldUtil
    ) {}

    public function isCreatable(string $entityType, string $field, string $type): bool
    {
        return $this->getClassName($entityType, $field, $type) !== null;
    }

    /**
     * @return Validator<Entity>
     */
    public function create(string $entityType, string $field, string $type): Validator
    {
        $className = $this->getClassName($entityType, $field, $type);

        if (!$className) {
            throw new RuntimeException("No validator.");
        }

        return $this->injectableFactory->create($className);
    }

    /**
     * @return ?class-string<Validator<Entity>>
     */
    private function getClassName(string $entityType, string $field, string $type): ?string
    {
        /** @var ?string $fieldType */
        $fieldType = $this->fieldUtil->getEntityTypeFieldParam($entityType, $field, 'type');

        return
            $this->metadata->get(['entityDefs', $entityType, 'fields', $field, 'validatorClassNameMap', $type]) ??
            $this->metadata->get(['fields', $fieldType ?? '', 'validatorClassNameMap', $type]);
    }

    /**
     * @return Validator<Entity>[]
     */
    public function createAdditionalList(string $entityType, string $field): array
    {
        /** @var class-string<Validator<Entity>>[] $classNameList */
        $classNameList = $this->metadata
            ->get(['entityDefs', $entityType, 'fields', $field, 'validatorClassNameList']) ?? [];

        $list = [];

        foreach ($classNameList as $className) {
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }
}
