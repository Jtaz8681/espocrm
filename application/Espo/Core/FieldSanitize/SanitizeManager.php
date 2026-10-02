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

namespace Espo\Core\FieldSanitize;

use Espo\Core\FieldSanitize\Sanitizer\Data;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use stdClass;

class SanitizeManager
{
    public function __construct(
        private Metadata $metadata,
        private FieldUtil $fieldUtil,
        private InjectableFactory $injectableFactory
    ) {}

    public function process(string $entityType, stdClass $rawData): void
    {
        $data = new Data($rawData);

        foreach ($this->fieldUtil->getEntityTypeFieldList($entityType) as $field) {
            if (!$this->isFieldSetInData($entityType, $field, $rawData)) {
                continue;
            }

            $this->processField($entityType, $field, $data);
        }
    }

    private function processField(string $entityType, string $field, Data $data): void
    {
        foreach ($this->getSanitizerList($entityType, $field) as $sanitizer) {
            $sanitizer->sanitize($data, $field);
        }
    }

    private function isFieldSetInData(string $entityType, string $field, stdClass $data): bool
    {
        $attributeList = $this->fieldUtil->getActualAttributeList($entityType, $field);

        $isSet = false;

        foreach ($attributeList as $attribute) {
            if (property_exists($data, $attribute)) {
                $isSet = true;

                break;
            }
        }

        return $isSet;
    }

    /**
     * @return Sanitizer[]
     */
    private function getSanitizerList(string $entityType, string $field): array
    {
        $classNameList = $this->getClassNameList($entityType, $field);

        return array_map(
            fn ($className) => $this->injectableFactory->createWith($className, ['entityType' => $entityType]),
            $classNameList
        );
    }

    /**
     * @return class-string<Sanitizer>[]
     */
    private function getClassNameList(string $entityType, string $field): array
    {
        $fieldType = $this->fieldUtil->getFieldType($entityType, $field);

        if (!$fieldType) {
            return [];
        }

        $classNameList = [];

        /** @var ?class-string<Sanitizer> $className */
        $className = $this->metadata->get("fields.$fieldType.sanitizerClassName");

        if ($className) {
            $classNameList[] = $className;
        }

        /** @var class-string<Sanitizer>[] $typeClassNameList */
        $typeClassNameList = $this->metadata->get("fields.$fieldType.sanitizerClassNameList") ?? [];

        $classNameList = array_merge($classNameList, $typeClassNameList);

        /** @var class-string<Sanitizer>[] $fieldClassNameList */
        $fieldClassNameList = $this->metadata->get("entityDefs.$entityType.fields.$field.sanitizerClassNameList") ?? [];
        $ignoreList = $this->metadata->get("entityDefs.$entityType.fields.$field.sanitizerSuppressClassNameList") ?? [];

        $list = array_merge($classNameList, $fieldClassNameList);

        if ($ignoreList === []) {
            return $list;
        }

        $list = array_diff($list, $ignoreList);

        return array_values($list);
    }
}
