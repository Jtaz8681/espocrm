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

namespace Espo\Core\Sms;

use Espo\Core\Binding\Factory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;

use RuntimeException;

/**
 * @implements Factory<Sender>
 */
class SenderFactory implements Factory
{
    public function __construct(
        private Config $config,
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    public function create(): Sender
    {
        $provider = $this->config->get('smsProvider');

        if (!$provider) {
            throw new RuntimeException("No `smsProvider` in config.");
        }

        /** @var ?class-string<Sender> $className */
        $className = $this->metadata->get(['app', 'smsProviders', $provider, 'senderClassName']);

        if (!$className) {
            throw new RuntimeException("No `senderClassName` for '$provider' provider.");
        }

        return $this->injectableFactory->create($className);
    }
}
