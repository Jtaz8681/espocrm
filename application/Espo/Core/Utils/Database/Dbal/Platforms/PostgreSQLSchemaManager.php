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

namespace Espo\Core\Utils\Database\Dbal\Platforms;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager as BasePostgreSQLSchemaManager;

class PostgreSQLSchemaManager extends BasePostgreSQLSchemaManager
{
    /**
     * DBAL does not add the 'fulltext' flag on reverse engineering.
     */
    protected function _getPortableTableIndexesList($tableIndexes, $tableName = null)
    {
        $indexes = parent::_getPortableTableIndexesList($tableIndexes, $tableName);

        foreach ($tableIndexes as $row) {
            $key = $row['relname'];

            if (is_string($tableName) && str_starts_with($tableName, '"') && str_ends_with($tableName, '"')) {
                $tableName = substr($tableName, 1, -1);
            }

            if ($key === "idx_{$tableName}_system_full_text_search") {
                $sql = "SELECT indexdef FROM pg_indexes WHERE indexname = '{$key}'";

                $rows = $this->_conn->fetchAllAssociative($sql);

                if (!$rows) {
                    continue;
                }

                $columns = self::parseColumnsIndexFromDeclaration($rows[0]['indexdef']);

                $indexes[$key] = new Index(
                    $key,
                    $columns,
                    false,
                    false,
                    ['fulltext']
                );
            }
        }

        return $indexes;
    }

    /**
     * @return string[]
     */
    private static function parseColumnsIndexFromDeclaration(string $string): array
    {
        preg_match('/to_tsvector\((.*),(.*)\)/i', $string, $matches);

        if (!$matches || count($matches) < 3) {
            return [];
        }

        $part = $matches[2];

        $part = str_replace("|| ' '::text", '', $part);
        $part = str_replace("::text", '', $part);
        $part = str_replace(" ", '', $part);
        $part = str_replace("||", ' ', $part);
        $part = str_replace("(", '', $part);
        $part = str_replace(")", '', $part);

        $list = array_map(
            fn ($item) => trim($item),
            explode(' ', $part)
        );

        return $list;
    }
}
