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

namespace Espo\Core\Job;

use Espo\Core\InjectableFactory;

use RuntimeException;

class PreparatorFactory
{
    public function __construct(
        private MetadataProvider $metadataProvider,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Create a preparator.
     */
    public function create(string $name): Preparator
    {
        /** @var ?class-string<Preparator> $className */
        $className = $this->metadataProvider->getPreparatorClassName($name);

        if (!$className) {
            throw new RuntimeException("Preparator for job '$name' not found.");
        }

        return $this->injectableFactory->create($className);
    }
}
