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

namespace Espo\Core\Field\Address;

use RuntimeException;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;

class AddressFormatterFactory
{
    public function __construct(
        private AddressFormatterMetadataProvider $metadataProvider,
        private InjectableFactory $injectableFactory,
        private Config $config
    ) {}

    public function create(int $format): AddressFormatter
    {
        /** @var ?class-string<AddressFormatter> $className */
        $className = $this->metadataProvider->getFormatterClassName($format);

        if (!$className) {
            throw new RuntimeException("Unknown address format '{$format}'.");
        }

        return $this->injectableFactory->create($className);
    }

    public function createDefault(): AddressFormatter
    {
        $format = $this->config->get('addressFormat') ?? 1;

        return $this->create($format);
    }
}
