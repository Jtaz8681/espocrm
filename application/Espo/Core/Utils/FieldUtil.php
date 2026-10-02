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

use Espo\ORM\Defs\Params\FieldParam;

class FieldUtil
{
    private const PARAM_ACTUAL = 'actual';
    private const PARAM_NOT_ACTUAL = 'notActual';
    private const PARAM_NAMING = 'naming';

    private const NAMING_PREFIX = 'prefix';
    private const NAMING_SUFFIX = 'suffix';

    /** @var array<string, array<string, string[]>> */
    private $fieldByTypeListCache = [];

    public function __construct(private Metadata $metadata)
    {}

    /**
     * Get a field type.
     * @since 8.1.0
     */
    public function getFieldType(string $entityType, string $field): ?string
    {
        return $this->metadata->get("entityDefs.$entityType.fields.$field.type");
    }

    /**
     * @return string[]
     */
    private function getAttributeListByType(string $entityType, string $name, string $type): array
    {
        $fieldType = $this->getFieldType($entityType, $name);

        if (!$fieldType) {
            return [];
        }

        $defs = $this->metadata->get("fields.$fieldType");

        if (!$defs) {
            return [];
        }

        if (is_object($defs)) {
            $defs = get_object_vars($defs);
        }

        $output = [];

        if (!isset($defs[$type . 'Fields'])) {
            if ($type === self::PARAM_ACTUAL) {
                $output[] = $name;
            }

            return $output;
        }

        $list = $defs[$type . 'Fields'];

        $naming = self::NAMING_SUFFIX;

        if (isset($defs[self::PARAM_NAMING])) {
            $naming = $defs[self::PARAM_NAMING];
        }

        if ($naming === self::NAMING_PREFIX) {
            foreach ($list as $it) {
                if ($it === '') {
                    $output[] = $name;
                } else {
                    $output[] = $it . ucfirst($name);
                }
            }
        } else {
            foreach ($list as $it) {
                $output[] = $name . ucfirst($it);
            }
        }

        return $output;
    }

    /**
     * @return string[]
     */
    public function getAdditionalActualAttributeList(string $entityType, string $name): array
    {
        $list = $this->metadata->get(['entityDefs', $entityType, 'fields', $name, 'additionalAttributeList']);

        if (!$list) {
            return [];
        }

        $type = $this->metadata->get(['entityDefs', $entityType, 'fields', $name, FieldParam::TYPE]);

        if (!$type) {
            return [];
        }

        $naming = $this->metadata->get("fields.$type.naming") ?? self::NAMING_SUFFIX;

        $output = [];

        if ($naming === self::NAMING_PREFIX) {
            foreach ($list as $it) {
                $output[] = $it . ucfirst($name);
            }
        } else {
            foreach ($list as $it) {
                $output[] = $name . ucfirst($it);
            }
        }

        return $output;
    }

    /**
     * Get storable attributes of a specific field.
     *
     * @return string[]
     */
    public function getActualAttributeList(string $entityType, string $field): array
    {
        return array_merge(
            $this->getAttributeListByType($entityType, $field, self::PARAM_ACTUAL),
            $this->getAdditionalActualAttributeList($entityType, $field),
            $this->getFullNameAdditionalActualAttributeList($entityType, $field),
        );
    }

    /**
     * Get non-storable attributes of a specific field.
     *
     * @return string[]
     */
    public function getNotActualAttributeList(string $entityType, string $field): array
    {
        return $this->getAttributeListByType($entityType, $field, self::PARAM_NOT_ACTUAL);
    }

    /**
     * Get attributes of a specific field.
     *
     * @return string[]
     */
    public function getAttributeList(string $entityType, string $field): array
    {
        return array_merge(
            $this->getActualAttributeList($entityType, $field),
            $this->getNotActualAttributeList($entityType, $field)
        );
    }

    /**
     * Get a list of fields of a specific type in an entity type.
     *
     * @return string[]
     */
    public function getFieldByTypeList(string $entityType, string $type): array
    {
        if (!array_key_exists($entityType, $this->fieldByTypeListCache)) {
            $this->fieldByTypeListCache[$entityType] = [];
        }

        if (!array_key_exists($type, $this->fieldByTypeListCache[$entityType])) {
            /** @var array<string, array<string, mixed>> $fieldDefs */
            $fieldDefs = $this->metadata->get(['entityDefs', $entityType, 'fields'], []);

            $list = [];

            foreach ($fieldDefs as $field => $defs) {
                if (isset($defs['type']) && $defs['type'] === $type) {
                    $list[] = $field;
                }
            }

            $this->fieldByTypeListCache[$entityType][$type] = $list;
        }

        return $this->fieldByTypeListCache[$entityType][$type];
    }

    /**
     * @return string[]
     */
    private function getFieldTypeAttributeListByType(string $fieldType, string $name, string $type): array
    {
        /** @var ?array<string, mixed> $defs */
        $defs = $this->metadata->get(['fields', $fieldType]);

        if (!$defs) {
            return [];
        }

        $output = [];

        if (!isset($defs[$type . 'Fields'])) {
            if ($type === self::PARAM_ACTUAL) {
                $output[] = $name;
            }

            return $output;
        }

        $list = $defs[$type . 'Fields'];

        $naming = self::NAMING_SUFFIX;

        if (isset($defs['naming'])) {
            $naming = $defs['naming'];
        }

        if ($naming === self::NAMING_PREFIX) {
            foreach ($list as $f) {
                $output[] = $f . ucfirst($name);
            }
        } else {
            foreach ($list as $f) {
                $output[] = $name . ucfirst($f);
            }
        }

        return $output;
    }

    /**
     * Get an attribute list for a given field type and field name.
     *
     * @return string[]
     */
    public function getFieldTypeAttributeList(string $fieldType, string $name): array
    {
        return array_merge(
            $this->getFieldTypeAttributeListByType($fieldType, $name, self::PARAM_ACTUAL),
            $this->getFieldTypeAttributeListByType($fieldType, $name, self::PARAM_NOT_ACTUAL)
        );
    }

    /**
     * Get a list of fields in an entity type.
     *
     * @return string[]
     */
    public function getEntityTypeFieldList(string $entityType): array
    {
        /** @var string[] */
        return array_keys($this->metadata->get(['entityDefs', $entityType, 'fields'], []));
    }

    /**
     * @return mixed
     */
    public function getEntityTypeFieldParam(string $entityType, string $field, string $param)
    {
        return $this->metadata->get(['entityDefs', $entityType, 'fields', $field, $param]);
    }

    /**
     * @return string[]
     */
    public function getEntityTypeAttributeList(string $entityType): array
    {
        $attributeList = [];

        foreach ($this->getEntityTypeFieldList($entityType) as $field) {
            $attributeList = array_merge(
                $attributeList,
                $this->getAttributeList($entityType, $field)
            );
        }

        return $attributeList;
    }

    /**
     * Get a field an attribute belongs to.
     *
     * @since 9.1.0
     */
    public function getFieldOfAttribute(string $entityType, string $attribute): ?string
    {
        foreach ($this->getEntityTypeFieldList($entityType) as $field) {
            if (in_array($attribute, $this->getAttributeList($entityType, $field))) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getFullNameAdditionalActualAttributeList(string $entityType, string $field): array
    {
        /** @var string[] */
        return $this->metadata->get("entityDefs.$entityType.fields.$field.fullNameAdditionalAttributeList") ?? [];
    }
}
