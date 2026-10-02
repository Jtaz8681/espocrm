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
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;

use stdClass;

/**
 * @noinspection PhpUnused
 */
class LinkMultipleType
{
    private const COLUMN_TYPE_ENUM = 'enum';
    private const COLUMN_TYPE_VARCHAR = 'varchar';
    private const COLUMN_TYPE_BOOL = 'bool';

    public function __construct(private Metadata $metadata, private Defs $defs)
    {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        if (!$entity instanceof CoreEntity) {
            return false;
        }

        /** @var string[] $idList */
        $idList = $entity->getLinkMultipleIdList($field);

        return count($idList) > 0;
    }

    /** @noinspection PhpUnused */
    public function checkPattern(Entity $entity, string $field): bool
    {
        /** @var ?mixed[] $idList */
        $idList = $entity->get($field . 'Ids');

        if ($idList === null || $idList === []) {
            return true;
        }

        $pattern = $this->metadata->get(['app', 'regExpPatterns', 'id', 'pattern']);

        if (!$pattern) {
            return true;
        }

        $preparedPattern = '/^' . $pattern . '$/';

        foreach ($idList as $id) {
            if (!is_string($id)) {
                return false;
            }

            if (!preg_match($preparedPattern, $id)) {
                return false;
            }
        }

        return true;
    }

    /** @noinspection PhpUnused */
    public function checkMaxCount(Entity $entity, string $field, ?int $maxCount): bool
    {
        if ($maxCount === null) {
            return true;
        }

        $list = $entity->get($field . 'Ids');

        if (!is_array($list)) {
            return true;
        }

        if (count($list) > $maxCount) {
            return false;
        }

        return true;
    }

    /** @noinspection PhpUnused */
    public function checkColumnsValid(Entity $entity, string $field): bool
    {
        if (!$entity instanceof CoreEntity) {
            return true;
        }

        if (!$entity->has($field . 'Columns')) {
            return true;
        }

        /** @var ?stdClass $columnsData */
        $columnsData = $entity->get($field . 'Columns');

        if ($columnsData === null) {
            return true;
        }

        $entityDefs = $this->defs->getEntity($entity->getEntityType());
        $fieldDefs = $entityDefs->getField($field);

        if ($fieldDefs->isNotStorable()) {
            return true;
        }

        /** @var ?array<string, string> $columnsMap */
        $columnsMap = $fieldDefs->getParam('columns');

        if ($columnsMap === null || $columnsMap === []) {
            return true;
        }

        if (!$entityDefs->hasRelation($field)) {
            return true;
        }

        $relationDefs = $entityDefs->getRelation($field);

        if (!$relationDefs->hasForeignEntityType()) {
            return true;
        }

        $foreignEntityType = $relationDefs->getForeignEntityType();

        foreach (array_keys(get_object_vars($columnsData)) as $id) {
            $itemData = $columnsData->$id;

            if (!$itemData instanceof stdClass) {
                return false;
            }

            foreach ($columnsMap as $column => $foreignField) {
                if (!property_exists($itemData, $column)) {
                    continue;
                }

                $value = $itemData->$column;

                $result = $this->checkColumnValue($foreignEntityType, $foreignField, $value);

                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    private function checkColumnValue(string $entityType, string $field, $value): bool
    {
        $fieldDefs = $this->defs
            ->getEntity($entityType)
            ->getField($field);

        $type = $fieldDefs->getType();

        if ($type === self::COLUMN_TYPE_VARCHAR) {
            return $this->checkColumnValueVarchar($fieldDefs, $value);
        }

        if ($type === self::COLUMN_TYPE_ENUM) {
            return $this->checkColumnValueEnum($fieldDefs, $value);
        }

        if ($type === self::COLUMN_TYPE_BOOL) {
            return is_bool($value);
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    private function checkColumnValueVarchar(Defs\FieldDefs $fieldDefs, $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $maxLength = $fieldDefs->getParam(FieldParam::MAX_LENGTH);
        $pattern = $fieldDefs->getParam('pattern');

        if ($maxLength && mb_strlen($value) > $maxLength) {
            return false;
        }

        if ($pattern) {
            if ($pattern[0] === '$') {
                $patternName = substr($pattern, 1);

                $pattern = $this->metadata
                    ->get(['app', 'regExpPatterns', $patternName, 'pattern']) ??
                    $pattern;
            }

            $preparedPattern = '/^' . $pattern . '$/';

            if (!preg_match($preparedPattern, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    private function checkColumnValueEnum(Defs\FieldDefs $fieldDefs, $value): bool
    {
        if (!is_string($value) && $value !== null) {
            return false;
        }

        /** @var ?string $path */
        $path = $fieldDefs->getParam('optionsPath');

        /** @var string[]|null|false $optionList */
        $optionList = $path ?
            $this->metadata->get($path) :
            $fieldDefs->getParam('options');

        if ($optionList === null) {
            return true;
        }

        // For bc.
        if ($optionList === false) {
            return true;
        }

        $optionList = array_map(
            fn ($item) => $item === '' ? null : $item,
            $optionList
        );

        return in_array($value, $optionList);
    }
}
