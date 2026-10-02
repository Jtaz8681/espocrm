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

use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Schema\Column as Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Espo\Core\Utils\Database\Dbal\Types\LongtextType;
use Espo\Core\Utils\Database\Dbal\Types\MediumtextType;

class DiffModifier
{
    /**
     * @param RebuildMode::* $mode
     * @throws DbalException
     */
    public function modify(
        SchemaDiff $diff,
        Schema $schema,
        bool $secondRun = false,
        string $mode = RebuildMode::SOFT
    ): bool {

        $reRun = false;
        $isHard = $mode === RebuildMode::HARD;

        $diff = $this->handleRemovedSequences($diff, $schema);

        $diff->removedTables = [];

        foreach ($diff->changedTables as $tableDiff) {
            $reRun = $this->amendTableDiff($tableDiff, $secondRun, $isHard) || $reRun;
        }

        return $reRun;
    }

    /**
     * @throws DbalException
     */
    private function amendTableDiff(TableDiff $tableDiff, bool $secondRun, bool $isHard): bool
    {
        $reRun = false;

        /**
         * @todo Leave only for MariaDB?
         * MariaDB supports RENAME INDEX as of v10.5.
         * Find out how long does it take to rename for different databases.
         */

        if (!$isHard) {
            // Prevent index renaming as an operation may take a lot of time.
            $tableDiff->renamedIndexes = [];
        }

        foreach ($tableDiff->removedColumns as $name => $column) {
            $reRun = $this->moveRemovedAutoincrementColumnToChanged($tableDiff, $column, $name) || $reRun;
        }

        if (!$isHard) {
            // Prevent column removal to prevent data loss.
            $tableDiff->removedColumns = [];
        }

        // Prevent column renaming as a not desired behavior.
        foreach ($tableDiff->renamedColumns as $renamedColumn) {
            $addedName = strtolower($renamedColumn->getName());
            $tableDiff->addedColumns[$addedName] = $renamedColumn;
        }

        $tableDiff->renamedColumns = [];

        foreach ($tableDiff->addedColumns as $column) {
            // Suppress autoincrement as need having a unique index first.
            $reRun = $this->amendAddedColumnAutoincrement($column) || $reRun;
        }

        foreach ($tableDiff->changedColumns as $name => $columnDiff) {
            if (!$isHard) {
                // Prevent decreasing length for string columns to prevent data loss.
                $this->amendColumnDiffLength($tableDiff, $columnDiff, $name);
                // Prevent longtext => mediumtext to prevent data loss.
                $this->amendColumnDiffTextType($tableDiff, $columnDiff, $name);
                // Prevent changing collation.
                $this->amendColumnDiffCollation($tableDiff, $columnDiff, $name);
                // Prevent changing charset.
                $this->amendColumnDiffCharset($tableDiff, $columnDiff, $name);
            }

            // Prevent setting autoincrement in first run.
            if (!$secondRun) {
                $reRun = $this->amendColumnDiffAutoincrement($tableDiff, $columnDiff, $name) || $reRun;
            }
        }

        return $reRun;
    }

    private function amendColumnDiffLength(TableDiff $tableDiff, ColumnDiff $columnDiff, string $name): void
    {
        $fromColumn = $columnDiff->fromColumn;
        $column = $columnDiff->column;

        if (!$fromColumn) {
            return;
        }

        if (!in_array('length', $columnDiff->changedProperties)) {
            return;
        }

        $fromLength = $fromColumn->getLength() ?? 255;
        $length = $column->getLength() ?? 255;

        if ($fromLength <= $length) {
            return;
        }

        $column->setLength($fromLength);

        self::unsetChangedColumnProperty($tableDiff, $columnDiff, $name, 'length');
    }

    /**
     * @throws DbalException
     */
    private function amendColumnDiffTextType(TableDiff $tableDiff, ColumnDiff $columnDiff, string $name): void
    {
        $fromColumn = $columnDiff->fromColumn;
        $column = $columnDiff->column;

        if (!$fromColumn) {
            return;
        }

        if (!in_array('type', $columnDiff->changedProperties)) {
            return;
        }

        $fromType = $fromColumn->getType();
        $type = $column->getType();

        if (
            !$fromType instanceof TextType ||
            !$type instanceof TextType
        ) {
            return;
        }

        $typePriority = [
            Types::TEXT,
            MediumtextType::NAME,
            LongtextType::NAME,
        ];

        $fromIndex = array_search($fromType->getName(), $typePriority);
        $index = array_search($type->getName(), $typePriority);

        if ($index >= $fromIndex) {
            return;
        }

        $column->setType(Type::getType($fromType->getName()));

        self::unsetChangedColumnProperty($tableDiff, $columnDiff, $name, 'type');
    }

    private function amendColumnDiffCollation(TableDiff $tableDiff, ColumnDiff $columnDiff, string $name): void
    {
        $fromColumn = $columnDiff->fromColumn;
        $column = $columnDiff->column;

        if (!$fromColumn) {
            return;
        }

        if (!in_array('collation', $columnDiff->changedProperties)) {
            return;
        }

        $fromCollation = $fromColumn->getPlatformOption('collation');

        if (!$fromCollation) {
            return;
        }

        $column->setPlatformOption('collation', $fromCollation);

        self::unsetChangedColumnProperty($tableDiff, $columnDiff, $name, 'collation');
    }

    private function amendColumnDiffCharset(TableDiff $tableDiff, ColumnDiff $columnDiff, string $name): void
    {
        $fromColumn = $columnDiff->fromColumn;
        $column = $columnDiff->column;

        if (!$fromColumn) {
            return;
        }

        if (!in_array('charset', $columnDiff->changedProperties)) {
            return;
        }

        $fromCharset = $fromColumn->getPlatformOption('charset');

        if (!$fromCharset) {
            return;
        }

        $column->setPlatformOption('charset', $fromCharset);

        self::unsetChangedColumnProperty($tableDiff, $columnDiff, $name, 'charset');
    }

    private function amendColumnDiffAutoincrement(TableDiff $tableDiff, ColumnDiff $columnDiff, string $name): bool
    {
        $fromColumn = $columnDiff->fromColumn;
        $column = $columnDiff->column;

        if (!$fromColumn) {
            return false;
        }

        if (!in_array('autoincrement', $columnDiff->changedProperties)) {
            return false;
        }

        $column
            ->setAutoincrement(false)
            ->setNotnull(false)
            ->setDefault(null);

        if ($name === 'id') {
            $column->setNotnull(true);
        }

        self::unsetChangedColumnProperty($tableDiff, $columnDiff, $name, 'autoincrement');

        return true;
    }

    private function amendAddedColumnAutoincrement(Column $column): bool
    {
        if (!$column->getAutoincrement()) {
            return false;
        }

        $column
            ->setAutoincrement(false)
            ->setNotnull(false)
            ->setDefault(null);

        return true;
    }

    private function moveRemovedAutoincrementColumnToChanged(TableDiff $tableDiff, Column $column, string $name): bool
    {
        if (!$column->getAutoincrement()) {
            return false;
        }

        $newColumn = clone $column;

        $newColumn
            ->setAutoincrement(false)
            ->setNotnull(false)
            ->setDefault(null);

        $changedProperties = [
            'autoincrement',
            'notnull',
            'default',
        ];

        $tableDiff->changedColumns[$name] = new ColumnDiff($name, $newColumn, $changedProperties, $column);

        foreach ($tableDiff->removedIndexes as $indexName => $index) {
            if ($index->getColumns() === [$name]) {
                unset($tableDiff->removedIndexes[$indexName]);
            }
        }

        return true;
    }

    private static function unsetChangedColumnProperty(
        TableDiff $tableDiff,
        ColumnDiff $columnDiff,
        string $name,
        string $property
    ): void {

        if (count($columnDiff->changedProperties) === 1) {
            unset($tableDiff->changedColumns[$name]);
        }

        $columnDiff->changedProperties = array_diff($columnDiff->changedProperties, [$property]);
    }

    /**
     * DBAL does not handle autoincrement columns that are not primary keys,
     * making them dropped.
     */
    private function handleRemovedSequences(SchemaDiff $diff, Schema $schema): SchemaDiff
    {
        $droppedSequences = $diff->getDroppedSequences();

        if ($droppedSequences === []) {
            return $diff;
        }

        foreach ($droppedSequences as $i => $sequence) {
            foreach ($schema->getTables() as $table) {
                $namespace = $table->getNamespaceName();
                $tableName = $table->getShortestName($namespace);

                foreach ($table->getColumns() as $column) {
                    if (!$column->getAutoincrement()) {
                        continue;
                    }

                    $sequenceName = $sequence->getShortestName($namespace);

                    $tableSequenceName = sprintf('%s_%s_seq', $tableName, $column->getShortestName($namespace));

                    if ($tableSequenceName !== $sequenceName) {
                        continue;
                    }

                    unset($droppedSequences[$i]);

                    continue 3;
                }
            }
        }

        $diff->removedSequences = array_values($droppedSequences);

        return $diff;
    }
}
