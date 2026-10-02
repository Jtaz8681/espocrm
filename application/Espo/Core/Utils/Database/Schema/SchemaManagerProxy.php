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

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Database\Helper;

use Doctrine\DBAL\Schema\SchemaException;

class SchemaManagerProxy
{
    private ?SchemaManager $schemaManager = null;

    public function __construct(private InjectableFactory $injectableFactory) {}

    private function getSchemaManager(): SchemaManager
    {
        $this->schemaManager ??= $this->injectableFactory->create(SchemaManager::class);

        return $this->schemaManager;
    }

    /**
     * @param ?string[] $entityTypeList
     * @param RebuildMode::* $mode
     * @throws SchemaException
     */
    public function rebuild(?array $entityTypeList = null, string $mode = RebuildMode::SOFT): bool
    {
        return $this->getSchemaManager()->rebuild($entityTypeList, $mode);
    }

    public function getDatabaseHelper(): Helper
    {
        return $this->getSchemaManager()->getDatabaseHelper();
    }
}
