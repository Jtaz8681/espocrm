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

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Metadata;
use RuntimeException;

class ColumnPreparatorFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private Helper $helper
    ) {}

    public function create(string $platform): ColumnPreparator
    {
        /** @var ?class-string<ColumnPreparator> $className */
        $className = $this->metadata
            ->get(['app', 'databasePlatforms', $platform, 'columnPreparatorClassName']);

        if (!$className) {
            throw new RuntimeException("No Column-Preparator for {$platform}.");
        }

        $binding = BindingContainerBuilder::create()
            ->bindInstance(Helper::class, $this->helper)
            ->build();

        return $this->injectableFactory->createWithBinding($className, $binding);
    }
}
