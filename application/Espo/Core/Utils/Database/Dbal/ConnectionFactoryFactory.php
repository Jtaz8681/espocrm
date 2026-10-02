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

namespace Espo\Core\Utils\Database\Dbal;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use PDO;
use RuntimeException;

class ConnectionFactoryFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    public function create(string $platform, PDO $pdo): ConnectionFactory
    {
        /** @var ?class-string<ConnectionFactory> $className */
        $className = $this->metadata
            ->get(['app', 'databasePlatforms', $platform, 'dbalConnectionFactoryClassName']);

        if (!$className) {
            throw new RuntimeException("No DBAL ConnectionFactory for {$platform}.");
        }

        $bindingContainer = BindingContainerBuilder::create()
            ->bindInstance(PDO::class, $pdo)
            ->build();

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }
}
