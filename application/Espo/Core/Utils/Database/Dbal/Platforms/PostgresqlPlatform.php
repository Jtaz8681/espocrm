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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

class PostgresqlPlatform extends PostgreSQL100Platform
{
    private const TEXT_SEARCH_CONFIG = 'pg_catalog.simple';

    private ?string $textSearchConfig;

    public function setTextSearchConfig(?string $textSearchConfig): void
    {
        $this->textSearchConfig = $textSearchConfig;
    }

    public function createSchemaManager(Connection $connection): PostgreSQLSchemaManager
    {
        return new PostgreSQLSchemaManager($connection, $this);
    }

    public function getCreateIndexSQL(Index $index, $table)
    {
        if (!$index->hasFlag('fulltext')) {
            return parent::getCreateIndexSQL($index, $table);
        }

        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }

        $name = $index->getQuotedName($this);
        $columns = $index->getColumns();

        if (count($columns) === 0) {
            throw new \InvalidArgumentException(sprintf(
                'Incomplete or invalid index definition %s on table %s',
                $name,
                $table,
            ));
        }

        $columnsPart = implode(" || ' ' || ", $index->getQuotedColumns($this));
        $partialPart = $this->getPartialIndexSQL($index);

        $textSearchConfig = $this->textSearchConfig ?? self::TEXT_SEARCH_CONFIG;
        $textSearchConfig = preg_replace('/[^A-Za-z0-9_.\-]+/', '', $textSearchConfig) ?? '';
        $configPart = $this->quoteStringLiteral($textSearchConfig);

        return "CREATE INDEX {$name} ON {$table} USING GIN (TO_TSVECTOR({$configPart}, {$columnsPart})) {$partialPart}";
    }
}
