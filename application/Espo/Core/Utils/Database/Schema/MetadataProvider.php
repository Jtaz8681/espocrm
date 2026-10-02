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

use Doctrine\DBAL\Types\Type;
use Espo\Core\Utils\Database\ConfigDataProvider;
use Espo\Core\Utils\Metadata;

class MetadataProvider
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private Metadata $metadata
    ) {}

    private function getPlatform(): string
    {
        return $this->configDataProvider->getPlatform();
    }

    /**
     * @return class-string<RebuildAction>[]
     */
    public function getPreRebuildActionClassNameList(): array
    {
        /** @var class-string<RebuildAction>[] */
        return $this->metadata
            ->get(['app', 'databasePlatforms', $this->getPlatform(), 'preRebuildActionClassNameList']) ?? [];
    }

    /**
     * @return class-string<RebuildAction>[]
     */
    public function getPostRebuildActionClassNameList(): array
    {
        /** @var class-string<RebuildAction>[] */
        return $this->metadata
            ->get(['app', 'databasePlatforms', $this->getPlatform(), 'postRebuildActionClassNameList']) ?? [];
    }

    /**
     * @return array<string, class-string<Type>>
     */
    public function getDbalTypeClassNameMap(): array
    {
        /** @var array<string, class-string<Type>> */
        return $this->metadata
            ->get(['app', 'databasePlatforms', $this->getPlatform(), 'dbalTypeClassNameMap']) ?? [];
    }
}
