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

namespace Espo\Core\Utils\Database\Schema\RebuildActions;

use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Schema\Schema as DbalSchema;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Database\Schema\RebuildAction;
use Espo\Core\Utils\Log;

use Exception;

class PrepareForFulltextIndex implements RebuildAction
{
    public function __construct(
        private Helper $helper,
        private Log $log
    ) {}

    /**
     * @throws DbalException
     */
    public function process(DbalSchema $oldSchema, DbalSchema $newSchema): void
    {
        if ($oldSchema->getTables() === []) {
            return;
        }

        $connection = $this->helper->getDbalConnection();
        $pdo = $this->helper->getPDO();

        foreach ($newSchema->getTables() as $table) {
            $tableName = $table->getName();
            $indexes = $table->getIndexes();

            foreach ($indexes as $index) {
                if (!$index->hasFlag('fulltext')) {
                    continue;
                }

                $columns = $index->getColumns();

                foreach ($columns as $columnName) {
                    $sql = "SHOW FULL COLUMNS FROM `" . $tableName . "` WHERE Field = " . $pdo->quote($columnName);

                    try {
                        /** @var array{Type: string, Collation: string} $row */
                        $row = $connection->fetchAssociative($sql);
                    } catch (Exception) {
                        continue;
                    }

                    switch (strtoupper($row['Type'])) {
                        case 'LONGTEXT':
                            $alterSql =
                                "ALTER TABLE `{$tableName}` " .
                                "MODIFY `{$columnName}` MEDIUMTEXT COLLATE " . $row['Collation'];

                            $this->log->info('SCHEMA, Execute Query: ' . $alterSql);

                            $connection->executeQuery($alterSql);

                            break;
                    }
                }
            }
        }
    }
}
