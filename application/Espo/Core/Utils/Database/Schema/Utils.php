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

namespace Espo\Core\Utils\Database\Schema;

use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Util;
use Espo\ORM\Defs\IndexDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\EntityParam;
use Espo\ORM\Defs\Params\IndexParam;

class Utils
{
    /**
     * Get indexes in specific format.
     * @deprecated
     *
     * @param array<string, mixed> $defs
     * @param string[] $ignoreFlags @todo Remove parameter?
     * @return array<string, array<string, mixed>>
     */
    public static function getIndexes(array $defs, array $ignoreFlags = []): array
    {
        $indexList = [];

        foreach ($defs as $entityType => $entityParams) {
            $indexes = $entityParams[EntityParam::INDEXES] ?? [];

            foreach ($indexes as $indexName => $indexParams) {
                $indexDefs = IndexDefs::fromRaw($indexParams, $indexName);

                $tableIndexName = $indexParams[IndexParam::KEY] ?? null;

                if (!$tableIndexName) {
                    continue;
                }

                $columns = $indexDefs->getColumnList();
                $flags = $indexDefs->getFlagList();

                if ($flags !== []) {
                    $skipIndex = false;

                    foreach ($ignoreFlags as $ignoreFlag) {
                        if (($flagKey = array_search($ignoreFlag, $flags)) !== false) {
                            unset($flags[$flagKey]);

                            $skipIndex = true;
                        }
                    }

                    if ($skipIndex && empty($flags)) {
                        continue;
                    }

                    $indexList[$entityType][$tableIndexName][IndexParam::FLAGS] = $flags;
                }

                if ($columns !== []) {
                    $indexType = self::getIndexTypeByIndexDefs($indexDefs);

                    // @todo Revise, may to be removed.
                    $indexList[$entityType][$tableIndexName][IndexParam::TYPE] = $indexType;

                    $indexList[$entityType][$tableIndexName][IndexParam::COLUMNS] = array_map(
                        fn ($item) => Util::toUnderScore($item),
                        $columns
                    );
                }
            }
        }

        /** @var array<string, array<string, mixed>> */
        return $indexList; /** @phpstan-ignore-line */
    }

    private static function getIndexTypeByIndexDefs(IndexDefs $indexDefs): string
    {
        if ($indexDefs->isUnique()) {
            return 'unique';
        }

        if (in_array('fulltext', $indexDefs->getFlagList())) {
            return 'fulltext';
        }

        return 'index';
    }

    /**
     * @deprecated
     *
     * @param array<string, mixed> $ormMeta
     * @param int $indexMaxLength
     * @param ?array<string, mixed> $indexList
     * @param int $characterLength
     * @return array<string, mixed>
     */
    public static function getFieldListExceededIndexMaxLength(
        array $ormMeta,
        $indexMaxLength = 1000,
        ?array $indexList = null,
        $characterLength = 4
    ) {

        $permittedFieldTypeList = [
            FieldType::VARCHAR,
        ];

        $fields = [];

        if (!isset($indexList)) {
            $indexList = self::getIndexes($ormMeta, ['fulltext']);
        }

        foreach ($indexList as $entityName => $indexes) {
            foreach ($indexes as $indexName => $indexParams) {
                $columnList = $indexParams['columns'];

                $indexLength = 0;

                foreach ($columnList as $columnName) {
                    $fieldName = Util::toCamelCase($columnName);

                    if (!isset($ormMeta[$entityName]['fields'][$fieldName])) {
                        continue;
                    }

                    $indexLength += self::getFieldLength(
                        $ormMeta[$entityName]['fields'][$fieldName],
                        $characterLength
                    );
                }

                if ($indexLength > $indexMaxLength) {
                    foreach ($columnList as $columnName) {
                        $fieldName = Util::toCamelCase($columnName);

                        if (!isset($ormMeta[$entityName]['fields'][$fieldName])) {
                            continue;
                        }

                        $fieldType = self::getFieldType($ormMeta[$entityName]['fields'][$fieldName]);

                        if (in_array($fieldType, $permittedFieldTypeList)) {
                            if (!isset($fields[$entityName]) || !in_array($fieldName, $fields[$entityName])) {
                                $fields[$entityName][] = $fieldName;
                            }
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $ormFieldDefs
     * @param int $characterLength
     * @return int
     */
    private static function getFieldLength(array $ormFieldDefs, $characterLength = 4)
    {
        $length = 0;

        if (isset($ormFieldDefs[AttributeParam::NOT_STORABLE]) && $ormFieldDefs[AttributeParam::NOT_STORABLE]) {
            return $length;
        }

        $defaultLength = [
            'datetime' => 8,
            'time' => 4,
            'int' => 4,
            'bool' => 1,
            'float' => 4,
            'varchar' => 255,
        ];

        $type = self::getDbFieldType($ormFieldDefs);

        $length = $defaultLength[$type] ?? $length;

        switch ($type) {
            case 'varchar':
                $length = $length * $characterLength;

                break;
        }

        return $length;
    }

    /**
     * @param array<string, mixed> $ormFieldDefs
     * @return string
     */
    private static function getDbFieldType(array $ormFieldDefs)
    {
        return $ormFieldDefs[AttributeParam::DB_TYPE] ?? $ormFieldDefs['type'];
    }

    /**
     * @param array<string, mixed> $ormFieldDefs
     */
    private static function getFieldType(array $ormFieldDefs): string
    {
        return $ormFieldDefs['type'] ?? self::getDbFieldType($ormFieldDefs);
    }
}
