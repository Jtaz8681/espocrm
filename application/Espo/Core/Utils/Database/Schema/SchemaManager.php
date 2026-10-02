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

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata\OrmMetadataData;

use Throwable;

/**
 * A database schema manager.
 */
class SchemaManager
{
    /** @var AbstractSchemaManager<AbstractPlatform> */
    private AbstractSchemaManager $schemaManager;
    private Comparator $comparator;
    private Builder $builder;

    /**
     * @throws DbalException
     */
    public function __construct(
        private OrmMetadataData $ormMetadataData,
        private Log $log,
        private Helper $helper,
        private MetadataProvider $metadataProvider,
        private DiffModifier $diffModifier,
        private InjectableFactory $injectableFactory
    ) {
        $this->schemaManager = $this->getDbalConnection()
            ->getDatabasePlatform()
            ->createSchemaManager($this->getDbalConnection());

        // Not using a platform specific comparator as it unsets a collation and charset if
        // they match a table default.
        //$this->comparator = $this->schemaManager->createComparator();
        $this->comparator = new Comparator($this->getPlatform());

        $this->initFieldTypes();

        $this->builder = $this->injectableFactory->createWithBinding(
            Builder::class,
            BindingContainerBuilder::create()
                ->bindInstance(Helper::class, $this->helper)
                ->build()
        );
    }

    public function getDatabaseHelper(): Helper
    {
        return $this->helper;
    }

    /**
     * @throws DbalException
     */
    private function getPlatform(): AbstractPlatform
    {
        return $this->getDbalConnection()->getDatabasePlatform();
    }

    private function getDbalConnection(): DbalConnection
    {
        return $this->helper->getDbalConnection();
    }

    /**
     * @throws DbalException
     */
    private function initFieldTypes(): void
    {
        foreach ($this->metadataProvider->getDbalTypeClassNameMap() as $type => $className) {
            Type::hasType($type) ?
                Type::overrideType($type, $className) :
                Type::addType($type, $className);

            $this->getDbalConnection()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping($type, $type);
        }
    }

    /**
     * Rebuild database schema. Creates and alters needed tables and columns.
     * Does not remove columns, does not decrease column lengths.
     *
     * @param ?string[] $entityTypeList Specific entity types.
     * @param RebuildMode::* $mode A mode.
     * @throws SchemaException
     * @throws DbalException
     * @todo Catch and re-throw exceptions.
     */
    public function rebuild(?array $entityTypeList = null, string $mode = RebuildMode::SOFT): bool
    {
        $fromSchema = $this->introspectSchema();
        $schema = $this->builder->build($this->ormMetadataData->getData(), $entityTypeList);

        try {
            $this->processPreRebuildActions($fromSchema, $schema);
        } catch (Throwable $e) {
            $this->log->alert('Rebuild database pre-rebuild error: '. $e->getMessage());

            return false;
        }

        $diff = $this->comparator->compareSchemas($fromSchema, $schema);
        $needReRun = $this->diffModifier->modify($diff, $schema, false, $mode);
        $sql = $this->composeDiffSql($diff);

        $result = $this->runSql($sql);

        if (!$result) {
            return false;
        }

        if ($needReRun) {
            // Needed to handle auto-increment column creation/removal/change.
            // As an auto-increment column requires having a unique index, but
            // Doctrine DBAL does not handle this.
            $intermediateSchema = $this->introspectSchema();
            $schema = $this->builder->build($this->ormMetadataData->getData(), $entityTypeList);

            $diff = $this->comparator->compareSchemas($intermediateSchema, $schema);

            $this->diffModifier->modify($diff, $schema, true);
            $sql = $this->composeDiffSql($diff);
            $result = $this->runSql($sql);
        }

        if (!$result) {
            return false;
        }

        try {
            $this->processPostRebuildActions($fromSchema, $schema);
        } catch (Throwable $e) {
            $this->log->alert('Rebuild database post-rebuild error: ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param string[] $queries
     * @return bool
     */
    private function runSql(array $queries): bool
    {
        $result = true;

        $connection = $this->getDbalConnection();

        foreach ($queries as $sql) {
            $this->log->info('Schema, query: '. $sql);

            try {
                $connection->executeQuery($sql);
            } catch (Throwable $e) {
                $this->log->alert('Rebuild database error: ' . $e->getMessage());

                $result = false;
            }
        }

        return $result;
    }

    /**
     * Introspect and return a current database schema.
     *
     * @throws DbalException
     */
    private function introspectSchema(): Schema
    {
        return $this->schemaManager->introspectSchema();
    }

    /**
     * @return string[]
     * @throws DbalException
     */
    private function composeDiffSql(SchemaDiff $diff): array
    {
        return $this->getPlatform()->getAlterSchemaSQL($diff);
    }

    private function processPreRebuildActions(Schema $actualSchema, Schema $schema): void
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(Helper::class, $this->helper)
            ->build();

        foreach ($this->metadataProvider->getPreRebuildActionClassNameList() as $className) {
            $action = $this->injectableFactory->createWithBinding($className, $binding);

            $action->process($actualSchema, $schema);
        }
    }

    private function processPostRebuildActions(Schema $actualSchema, Schema $schema): void
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(Helper::class, $this->helper)
            ->build();

        foreach ($this->metadataProvider->getPostRebuildActionClassNameList() as $className) {
            $action = $this->injectableFactory->createWithBinding($className, $binding);

            $action->process($actualSchema, $schema);
        }
    }
}
