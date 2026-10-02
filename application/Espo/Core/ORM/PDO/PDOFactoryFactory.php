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

namespace Espo\Core\ORM\PDO;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\PDO\PDOFactory;
use RuntimeException;

class PDOFactoryFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    public function create(string $platform): PDOFactory
    {
        /** @var ?class-string<PDOFactory> $className */
        $className =
            $this->metadata->get(['app', 'orm', 'platforms', $platform, 'pdoFactoryClassName']) ??
            $this->metadata->get(['app', 'orm', 'pdoFactoryClassNameMap', $platform]);

        if (!$className) {
            throw new RuntimeException("Could not create PDOFactory.");
        }

        return $this->injectableFactory->create($className);
    }
}
